<?php

namespace Database\Seeders;

use App\Models\Charity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
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
            'stripe_account_id' => 'acct_1Jyt5XHfJ1sl7zIL'
        ]);
        setPermissionsTeamId($charity->id);
        Role::updateOrCreate(['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_CHARITY_ADMIN]);
        Role::updateOrCreate(['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_CHARITY_STAFF]);
        $role = Role::updateOrCreate(['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_CHARITY_SUPER_ADMIN]);
        User::find(1)->assignRole($role);
    }
}
