<?php

namespace App\Imports;

use App\Models\Activity;
use Illuminate\Support\Collection;
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

        dd($data->whereNotNull('group')->pluck('group'));
    }
}
