<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Ticket::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        Ticket::create([
            'charity_id' => 1,
            'activity_id' => 1,
            'team_id' => 1,
            'table_num' => 1,
            'user_id' => 1,
            'type' => Ticket::TYPE_DONOR,
            'price' => 100,
            'amount' => 100,
        ]);
    }
}
