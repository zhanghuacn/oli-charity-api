<?php

namespace Database\Seeders;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        Activity::truncate();
        Activity::create([
            'charity_id' => 1,
            'name' => Str::random(10),
            'description' => Str::random(40),
            'content' => Str::random(100),
            'location' => 'beijing',
            'begin_time' => Carbon::now(),
            'end_time' => Carbon::now()->addDays(10),
            'price' => 100,
            'stocks' => 100,
        ]);
    }
}
