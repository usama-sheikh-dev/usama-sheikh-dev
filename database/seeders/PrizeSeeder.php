<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PrizeSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('user_prizes')->insert([
            [
                'contest_id' => 1,
                'user_id' => 3,
                'prize' => 'Gift Card'
            ]
        ]);
    }
}
