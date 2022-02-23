<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::truncate();
        Admin::create([
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'name' => '超级管理员',
            'password' => 'admin',
            'last_ip' => '127.0.0.1',
            'last_active_at' => Carbon::now()->tz(config('app.timezone')),
        ]);
    }
}
