<?php

namespace Database\Seeders;

use App\Models\User;
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
        $users = [
            'Admin' => [
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => Hash::make('123456789'),
            ],
            'VIP' => [
                'name' => 'VIP User',
                'email' => 'vip@test.com',
                'password' => Hash::make('123456789'),
            ],
            'Normal' => [
                'name' => 'Normal User',
                'email' => 'user@test.com',
                'password' => Hash::make('123456789'),
            ],
            'Guest' => [
                'name' => 'Guest User',
                'email' => 'guest@test.com',
                'password' => Hash::make('123456789'),
            ],
        ];

        foreach ($users as $key=>$user) {
            $newUser = User::create($user);
            $newUser->assignRole($key);
        }
    }
}
