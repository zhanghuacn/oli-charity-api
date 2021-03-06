<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        $this->call(UserSeeder::class);
//        $this->call(AdminSeeder::class);
//        $this->call(CharitySeeder::class);
//        $this->call(ActivitySeeder::class);
//        $this->call(SponsorSeeder::class);
//        $this->call(GoodsSeeder::class);
//        $this->call(GroupSeeder::class);
//        $this->call(TicketSeeder::class);
//        $this->call(LotterySeeder::class);
//        $this->call(PrizeSeeder::class);
//        $this->call(OrderSeeder::class);
        Activity::truncate();
        Lottery::truncate();
        Prize::truncate();
        Ticket::truncate();
    }
}
