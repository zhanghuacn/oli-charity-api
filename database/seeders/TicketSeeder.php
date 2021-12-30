<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    public function run()
    {
        Ticket::truncate();
        Ticket::create([
            'code' => Str::uuid(),
            'lottery_code' => '123456',
            'charity_id' => 1,
            'activity_id' => 1,
            'seat_num' => 1,
            'user_id' => 1,
            'type' => Ticket::TYPE_DONOR,
            'price' => 100,
            'amount' => 100,
        ]);
        Ticket::create([
            'code' => Str::uuid(),
            'lottery_code' => '654321',
            'charity_id' => 1,
            'activity_id' => 1,
            'seat_num' => 1,
            'user_id' => 2,
            'type' => Ticket::TYPE_DONOR,
            'price' => 100,
            'amount' => 200,
        ]);

        Ticket::find(1)->attachGroup(Group::find(1));
        Ticket::find(2)->attachGroup(Group::find(1));
    }
}
