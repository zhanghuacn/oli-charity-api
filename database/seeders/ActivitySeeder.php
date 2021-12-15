<?php

namespace Database\Seeders;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Nette\Utils\Random;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        Activity::truncate();
        Activity::create([
            'charity_id' => 1,
            'title' => Str::random(10),
            'description' => Str::random(40),
            'content' => Str::random(100),
            'location' => 'beijing',
            'begin_time' => Carbon::now(),
            'end_time' => Carbon::now()->addDays(10),
        ]);
    }
}
