<?php

namespace App\Imports;

use App\Models\Activity;
use App\Models\Group;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class TicketImport implements ToCollection
{
    protected Activity $activity;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    public function collection(Collection $collection)
    {
        $data = $collection->slice(5)->transform(function ($item) {
            return [
                'code' => $item[0],
                'first_name' => $item[1],
                'last_name' => $item[2],
                'username' => $item[3],
                'email' => $item[4],
                'phone' => $item[5],
                'group' => $item[6],
                'seat_num' => $item[7],
            ];
        });
        DB::transaction(function () use ($data) {
            $data->whereNotNull('group')->unique('group')->each(function ($item) {
                Group::updateOrCreate([
                    'charity_id' => $this->activity->charity_id,
                    'activity_id' => $this->activity->id,
                    'name' => $item['group'],
                ]);
            });
            $groups = $this->activity->groups->pluck('id', 'name');
            $data->each(function ($item) use ($groups) {
                Ticket::where(['activity_id' => $this->activity->id, 'code' => $item['code']])->update([
                    'seat_num' => $item['seat_num'],
                    'group_id' => $groups->get($item['group'])
                ]);
            });
        });
    }
}
