<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserEntrySeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('user_contest_entries')->insert([
            [
                'user_id' => 3,
                'contest_id' => 1,
                'status' => 'in_progress',
                'score' => 0
            ],
        ]);
    }
}
