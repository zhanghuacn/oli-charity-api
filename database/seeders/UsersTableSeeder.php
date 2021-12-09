<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        User::create([
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'name' => '超级管理员',
            'password' => 'admin'
        ]);
    }
}
