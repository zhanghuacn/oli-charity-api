<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sponsor::truncate();
        $sponsor = Sponsor::create([
            'name' => Str::random(10),
            'logo' => Str::random(10),
            'backdrop' => Str::random(10),
            'website' => 'https://www.qq.com',
            'description' => Str::random(40),
            'introduce' => Str::random(500),
            'contact' => Str::random(10),
            'phone' => '1311111111',
            'mobile' => '28766622',
            'email' => Str::random(10) . '@gmail.com',
        ]);
        $sponsor->staffs()->attach(2);
    }
}
