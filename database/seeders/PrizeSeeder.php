<?php

namespace Database\Seeders;

use App\Models\Lottery;
use App\Models\Prize;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Prize::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        Prize::create([
            'charity_id' => 1,
            'activity_id' => 1,
            'lottery_id' => 1,
            'name' => Str::random(10),
            'description' => Str::random(30),
            'num' => 5,
        ]);
    }
}
