<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ContestSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('contests')->insert([
            [
                'name' => 'General Knowledge Contest',
                'description' => 'Open for normal users',
                'access_level' => 'normal',
                'start_time' => now(),
                'end_time' => now()->addDays(2),
                'prize' => 'Gift Card',
            ],
            [
                'name' => 'VIP Exclusive Quiz',
                'description' => 'Only for VIP users',
                'access_level' => 'vip',
                'start_time' => now(),
                'end_time' => now()->addDays(2),
                'prize' => 'iPhone 15',
            ],
        ]);
    }
}
