<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAnswerSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('user_answers')->insert([
            [
                'entry_id' => 1,
                'question_id' => 1,
                'option_id' => 1
            ]
        ]);
    }
}
