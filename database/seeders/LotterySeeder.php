<?php

namespace Database\Seeders;

use App\Models\Lottery;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LotterySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Lottery::truncate();
        Lottery::create([
            'charity_id' => 1,
            'activity_id' => 1,
            'name' => Str::random(10),
            'description' => Str::random(30),
            'begin_time' => Carbon::now(),
            'end_time' => Carbon::now()->addDays(2),
            'standard_amount' => 100,
            'draw_time' => Carbon::now()->addDays(3),
        ]);
    }
}
