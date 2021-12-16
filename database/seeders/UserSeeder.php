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
        User::create([
            'username' => 'zhanghua',
            'email' => 'zhanghua@163.com',
            'name' => '张华',
            'password' => 'zhanghua',
            'profile' => Str::random(20),
        ]);
    }
}
