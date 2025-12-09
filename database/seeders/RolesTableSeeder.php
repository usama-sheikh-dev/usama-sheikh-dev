<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'VIP',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Normal',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Guest',
                'guard_name' => 'web',
            ],
        ];

        Role::insert($roles);
    }
}
