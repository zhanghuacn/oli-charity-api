<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Charity;
use App\Models\News;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class HomeController extends Controller
{
    public function search(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'keyword' => 'required|string',
        ]);
        $data = [
            'events' => Activity::filter($request->all())
                ->withCount('tickets')
                ->withSum('orders', 'amount')->limit(5)->get()
                ->transform(function (Activity $activity) {
                    return [
                        'id' => $activity->id,
                        'name' => $activity->name,
                        'description' => $activity->description,
                        'image' => collect($activity->images)->first(),
                        'location' => $activity->location,
                        'begin_time' => $activity->begin_time,
                        'end_time' => $activity->end_time,
                        'participates' => $activity->extends['participates'],
                        'total_income' => $activity->extends['total_amount'],
                    ];
                }),
            'news' => News::filter($request->all())->limit(5)->get()
                ->transform(function (News $news) {
                    return [
                        'id' => $news->id,
                        'title' => $news->title,
                        'image' => $news->thumb,
                        'description' => $news->description,
                    ];
                }),
            'staffs' => Charity::find(getPermissionsTeamId())->staffs()
                ->where('name', 'like', $request->get('keyword') . '%')
                ->orWhere('username', 'like', $request->get('keyword') . '%')
                ->orWhere('email', 'like', $request->get('keyword') . '%')->limit(5)->get()->transform(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'profile' => $user->profile,
                    ];
                }),
        ];
        return Response::success($data);
    }

    public function dashboard(): JsonResponse|JsonResource
    {
        $charity = Charity::findOrFail(getPermissionsTeamId());
        $data = [
            'events' => $this->events($charity),
            'staffs' => $this->staffs($charity),
            'followers' => $this->followers($charity),
            'received' => $this->received(),
            'sources' => $this->sources(),
        ];
        return Response::success($data);
    }

    private function received(): array
    {
        $order = Order::filter([
            'charity_id' => getPermissionsTeamId(),
            'payment_status' => Order::STATUS_PAID,
        ])->selectRaw('DATE_FORMAT(payment_time, "%m") as date, sum(amount) as total_amount')
            ->groupBy('date')->pluck('total_amount', 'date')->toArray();
        $total = 0;
        for ($i = 1; $i <= 12; $i++) {
            $total += $received[str_pad($i, 2, '0', STR_PAD_LEFT)] ?? 0;
            $data[] = $total;
        }
        return $data;
    }

    private function sources(): array
    {
        return Order::filter([
            'charity_id' => getPermissionsTeamId(),
            'payment_status' => Order::STATUS_PAID,
        ])->selectRaw('type, sum(amount) as total_amount')
            ->groupBy('type')->get()->toArray();
    }


    private function followers(Charity $charity): array|Collection
    {
        return $charity->favoriters->transform(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'profile' => $user->profile,
            ];
        });
    }

    private function events(Charity $charity): array|Collection
    {
        return $charity->activities->transform(function (Activity $activity) {
            return [
                'id' => $activity->id,
                'name' => $activity->name,
                'description' => $activity->description,
                'image' => collect($activity->images)->first(),
                'location' => $activity->location,
                'begin_time' => $activity->begin_time,
                'end_time' => $activity->end_time,
            ];
        });
    }

    private function staffs(Charity $charity): Collection|array
    {
        return $charity->staffs->transform(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'profile' => $user->profile,
            ];
        });
    }
}
