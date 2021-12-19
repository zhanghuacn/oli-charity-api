<?php

namespace Database\Seeders;

use App\Models\Charity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CharitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Charity::truncate();
        Charity::create([
            'name' => Str::random(10),
            'logo' => Str::random(10),
            'backdrop' => Str::random(10),
            'website' => 'https://www.qq.com',
            'description' => Str::random(40),
            'introduce' => Str::random(500),
            'staff_num' => 10,
            'contact' => Str::random(10),
            'phone' => '1311111111',
            'mobile' => '28766622',
            'email' => Str::random(10).'@gmail.com',
            'stripe_account' => 'acct_1Jyt5XHfJ1sl7zIL'
        ]);
    }
}
