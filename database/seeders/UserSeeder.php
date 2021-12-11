<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        User::create([
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'name' => '超级管理员',
            'password' => 'admin',
            'profile' => Str::random(20),
        ]);
    }
}
