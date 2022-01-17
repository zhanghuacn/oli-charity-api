<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\CharityCollection;
use App\Http\Resources\Api\NotificationCollection;
use App\Http\Resources\Api\UserCollection;
use App\Models\Activity;
use App\Models\Charity;
use App\Models\Order;
use App\Models\Sponsor;
use App\Models\User;
use App\Notifications\ApplyPaid;
use App\Notifications\InvitePaid;
use App\Notifications\LotteryPaid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Jiannei\Response\Laravel\Support\Facades\Response;

class UcenterController extends Controller
{
    public function notifications(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'type' => 'sometimes|in:LOTTERY,INVITE,APPLY',
            'event_id' => 'sometimes|integer|exists:activities,id',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Auth::user()->notifications()->when($request->has('type'), function (Builder $query) use ($request) {
            $query->where(
                'type',
                '=',
                match ($request->get('type')) {
                    'LOTTERY' => LotteryPaid::class,
                    'INVITE' => InvitePaid::class,
                    'APPLY' => ApplyPaid::class,
                }
            );
        })->when($request->has('event_id'), function (Builder $query) use ($request) {
            $query->where('data->activity_id', '=', $request->get('event_id'));
        })->simplePaginate($request->input('per_page', 15));
        return Response::success(new NotificationCollection($data));
    }

    public function update(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'avatar' => 'sometimes|url',
            'first_name' => 'sometimes|string',
            'middle_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'birthday' => 'sometimes|date',
            'name' => 'sometimes|string',
            'profile' => 'sometimes|string',
            'backdrop' => 'sometimes|url',
        ]);
        $user = Auth::user();
        $user->update($request->only(['avatar', 'backdrop', 'first_name', 'middle_name', 'last_name', 'birthday', 'name', 'profile']));
        $user->refresh();
        return Response::success([
            'id' => $user->id,
            'birthday' => Carbon::parse($user->birthday)->toDateString(),
            'gender' => $user->gender,
            'last_name' => $user->last_name,
            'middle_name' => $user->middle_name,
            'first_name' => $user->first_name,
            'profile' => $user->profile,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'is_public_records' => $user->extends['records'],
            'is_public_portfolio' => $user->extends['portfolio'],
            'backdrop' => $user->backdrop,
        ]);
    }

    public function privacy(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'portfolio' => 'required|boolean',
            'records' => 'required|boolean',
        ]);
        Auth::user()->update(['settings->portfolio' => $request['portfolio'], 'settings->records' => $request['records']]);
        Auth::user()->refresh();
        return Response::success(Auth::user()->settings);
    }

    public function activities(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'filter' => 'sometimes|in:CURRENT,UPCOMING,PAST',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $request->merge(['user_id' => Auth::id()]);
        $activities = Activity::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    public function chart(Request $request): JsonResponse|JsonResource
    {
        $request->merge([
            'user_id' => Auth::id(),
            'payment_status' => Order::STATUS_PAID,
        ]);
        $data['total_amount'] = Order::filter($request->all())->sum('amount');
        $received = Order::filter($request->all())->selectRaw('DATE_FORMAT(payment_time, "%m") as date, sum(amount) as total_amount')
            ->groupBy('date')->pluck('total_amount', 'date')->toArray();
        $total = 0;
        for ($i = 1; $i <= 12; $i++) {
            $total += $received[str_pad($i, 2, '0', STR_PAD_LEFT)] ?? 0;
            $data['received'][] = $total;
        }
        return Response::success($data);
    }

    public function followCharities(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Auth::user()->favorites()->withType(Charity::class)
            ->with('favoriteable')->simplePaginate($request->input('per_page', 15));
        $data->getCollection()->transform(function ($model) {
            return $model->favoriteable;
        });
        return Response::success(new CharityCollection($data));
    }

    public function followActivities(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Auth::user()->favorites()->withType(Activity::class)
            ->with('favoriteable')->simplePaginate($request->input('per_page', 15));
        $data->getCollection()->transform(function ($model) {
            return $model->favoriteable;
        });
        return Response::success(new ActivityCollection($data));
    }

    public function followUsers(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Auth::user()->followings()->simplePaginate($request->input('per_page', 15));
        return Response::success(new UserCollection($data));
    }

    public function charityToken(): JsonResponse|JsonResource
    {
        abort_if(Auth::user()->charities()->exists(), 422, 'Joined Charity');
        $data = [
            'type' => Charity::class,
            'expires' => now()->addDays(),
            'user_id' => Auth::id(),
        ];
        return Response::success([
            'token' => Crypt::encryptString(json_encode($data)),
        ]);
    }

    public function sponsorToken(): JsonResponse|JsonResource
    {
        abort_if(Auth::user()->sponsors()->exists(), 422, 'Joined Sponsor');
        $data = [
            'type' => Sponsor::class,
            'expires' => now()->addDays(),
            'user_id' => Auth::id(),
        ];
        return Response::success([
            'token' => Crypt::encryptString(json_encode($data)),
        ]);
    }
}
