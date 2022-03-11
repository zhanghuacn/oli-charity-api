<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GroupSeeder extends Seeder
{
    public function run()
    {
        Group::truncate();
        Group::create([
            'charity_id' => 1,
            'activity_id' => 1,
            'owner_id' => 1,
            'name' => Str::random(10),
            'description' => Str::random(20),
            'num' => 10,
        ]);
    }
}
