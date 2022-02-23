<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Charity;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Jiannei\Response\Laravel\Support\Facades\Response;

class HomeController extends Controller
{
    public function dashboard(): JsonResponse|JsonResource
    {
        $result = DB::table('users')->whereBetween('created_at', [Carbon::tz(config('app.timezone'))->now()->addDays(-7)->startOfDay(), Carbon::tz(config('app.timezone'))->now()->endOfDay()])
            ->selectRaw('date(created_at) as date,count(*) as num')->groupBy('date')->pluck('num', 'date')->toArray();
        for ($i = 7; $i >= 1; $i--) {
            if (!array_key_exists(Carbon::tz(config('app.timezone'))->now()->subDays($i)->toDateString(), $result)) {
                $result[Carbon::tz(config('app.timezone'))->now()->subDays($i)->toDateString()] = 0;
            }
        }
        ksort($result);
        $data = [
            'statistics' => [
                'members' => User::count(),
                'charities' => Charity::count(),
                'sponsors' => Sponsor::count(),
                'events' => Activity::count(),
            ],
            'charts' => $result,
        ];
        return Response::success($data);
    }
}
