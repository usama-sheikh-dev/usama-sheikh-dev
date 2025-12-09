<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class QuestionSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        $contest1 = 1;

        $q1 = DB::table('questions')->insertGetId([
            'contest_id' => $contest1,
            'question_text' => 'What is the capital of France?',
            'type' => 'single'
        ]);

        DB::table('question_options')->insert([
            ['question_id' => $q1, 'option_text' => 'Paris', 'is_correct' => true],
            ['question_id' => $q1, 'option_text' => 'London', 'is_correct' => false],
        ]);
    }
}
