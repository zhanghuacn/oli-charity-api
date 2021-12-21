<?php

namespace Database\Seeders;

use App\Models\Charity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
        $charity = Charity::create([
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
            'email' => Str::random(10) . '@gmail.com',
            'stripe_account' => 'acct_1Jyt5XHfJ1sl7zIL'
        ]);
        Role::updateOrCreate(['guard_name' => 'api', 'name' => 'super-admin', 'team_id' => 1]);
        setPermissionsTeamId($charity->id);
        User::find(1)->assignRole('super-admin');
    }
}
