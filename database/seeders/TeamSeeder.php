<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    public function run()
    {
        Team::truncate();
        Team::create([
            'charity_id' => 1,
            'activity_id' => 1,
            'user_id' => 1,
            'name' => Str::random(10),
            'description' => Str::random(20),
            'num' => 10,
        ]);
    }
}
