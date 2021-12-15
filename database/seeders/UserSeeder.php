<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::truncate();
        User::create([
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'name' => '超级管理员',
            'password' => 'admin',
            'profile' => Str::random(20),
        ]);
    }
}
