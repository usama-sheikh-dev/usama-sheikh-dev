<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => Hash::make('123456789'),
                'user_type' => 'admin'
            ],
            [
                'name' => 'VIP User',
                'email' => 'vip@test.com',
                'password' => Hash::make('123456789'),
                'user_type' => 'vip'
            ],
            [
                'name' => 'Normal User',
                'email' => 'user@test.com',
                'password' => Hash::make('123456789'),
                'user_type' => 'normal'
            ],
        ]);
    }
}
