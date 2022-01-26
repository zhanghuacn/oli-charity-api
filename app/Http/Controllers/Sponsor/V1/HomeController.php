<?php

namespace App\Http\Controllers\Sponsor\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Sponsor;
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
            'events' => Activity::filter($request->all())->whereHas('goods', function ($query) {
                return $query->where('goodsable_id', '=', getPermissionsTeamId())
                    ->where('goodsable_type', Sponsor::class);
            })->limit(3)->get()->transform(function (Activity $activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'description' => $activity->description,
                    'image' => collect($activity->images)->first(),
                    'location' => $activity->location,
                    'begin_time' => $activity->begin_time,
                    'end_time' => $activity->end_time,
                ];
            }),
            'products' => Goods::filter($request->all())->limit(6)->get()->transform(function (Goods $goods) {
                return [
                    'id' => $goods->id,
                    'name' => $goods->name,
                    'description' => $goods->description,
                    'image' => collect($goods->images)->first(),
                    'sold' => 0,
                    'income' => 0,
                ];
            }),
        ];
        return Response::success($data);
    }

    public function dashboard(Request $request): JsonResponse|JsonResource
    {
        $sponsor = Sponsor::findOrFail(getPermissionsTeamId());
        $data = [
            'events' => $this->events($sponsor),
            'goods' => $this->goods($sponsor),
            'received' => $this->received(),
            'sources' => $this->sources(),
        ];
        return Response::success($data);
    }

    private function received(): array
    {
        $received = Order::filter([
            'orderable_type' => Sponsor::class,
            'orderable_id' => getPermissionsTeamId(),
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
            'orderable_type' => Sponsor::class,
            'orderable_id' => getPermissionsTeamId(),
            'payment_status' => Order::STATUS_PAID,
        ])->selectRaw('type, sum(amount) as total_amount')
            ->groupBy('type')->get()->toArray();
    }

    private function goods(Sponsor $sponsor): Collection
    {
        return $sponsor->goods()->limit(6)->get()->transform(function (Goods $goods) {
            return [
                'id' => $goods->id,
                'name' => $goods->name,
                'description' => $goods->description,
                'image' => collect($goods->images)->first(),
                'sold' => 0,
                'income' => 0,
            ];
        });
    }

    private function events(Sponsor $sponsor): Collection|array
    {
        return Goods::whereHasMorph('goodsable', [Sponsor::class], function ($query) {
            $query->where('goodsable_id', '=', getPermissionsTeamId());
        })->with('activity')->get()->transform(function (Goods $goods) {
            return [
                'id' => optional($goods->activity)->id,
                'name' => optional($goods->activity)->name,
                'description' => optional($goods->activity)->description,
                'image' => collect(optional($goods->activity)->images)->first(),
                'location' => optional($goods->activity)->location,
                'begin_time' => optional($goods->activity)->begin_time,
                'end_time' => optional($goods->activity)->end_time,
            ];
        })->take(3);
    }
}
