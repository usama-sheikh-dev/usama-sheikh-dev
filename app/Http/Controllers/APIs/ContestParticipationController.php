<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContestEntryResource;
use App\Models\Contest;
use App\Models\QuestionOption;
use App\Models\UserAnswer;
use App\Models\UserContestEntry;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContestParticipationController extends BaseController
{
    // POST /contests/{id}/join
    public function join(Request $request, $id)
    {
        $user = $request->user();

        try {
            $contest = Contest::find($id);
            if (! $contest) {
                Log::info('Join contest failed - not found', ['contest_id' => $id]);
                return $this->sendError('Contest not found.', ['error' => 'Not found'], 404);
            }

            // Only allow non-guest users to join
            if (! $user->hasRole(['Admin','VIP','Normal'])) {
                Log::info('Join contest forbidden - guest user', ['user_id' => $user->id]);
                return $this->sendError('Guests cannot join contests. Please register.', ['error' => 'Guest'], 403);
            }

            // Check access level
            if ($contest->access_level === 'vip' && ! $user->hasRole(['VIP','Admin'])) {
                Log::info('Join contest forbidden - not VIP', ['user_id' => $user->id, 'contest_id' => $id]);
                return $this->sendError('Forbidden: VIP contest.', ['error' => 'Forbidden'], 403);
            }

            // Prevent joining after contest ended
            if (now()->gt($contest->end_time)) {
                Log::info('Join contest failed - contest ended', ['contest_id' => $id]);
                return $this->sendError('Contest has ended.', ['error' => 'Contest ended'], 400);
            }

            // Create or return existing entry
            $entry = UserContestEntry::firstOrCreate(
                ['user_id' => $user->id, 'contest_id' => $contest->id],
                ['status' => 'in_progress', 'score' => 0]
            );

            return $this->sendResponse($entry, 'Joined contest successfully.');
        } catch (Exception $e) {
            Log::info('Join contest error', ['error' => $e->getMessage(), 'contest_id' => $id]);
            return $this->sendError('Unable to join contest.', $e->getMessage(), 500);
        }
    }

    // POST /contests/{id}/submit
    public function submit(Request $request, $id)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer',
            'answers.*.selected_option_ids' => 'required|array|min:1',
            'answers.*.selected_option_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            Log::info('Submit validation error', ['errors' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }

        try {
            $contest = Contest::with('questions.options')->find($id);
            if (! $contest) {
                Log::info('Submit failed - contest not found', ['contest_id' => $id]);
                return $this->sendError('Contest not found.', ['error' => 'Not found'], 404);
            }

            // Access control and timing
            if ($contest->access_level === 'vip' && ! $user->hasRole(['VIP','Admin'])) {
                Log::info('Submit forbidden - not VIP', ['user_id' => $user->id, 'contest_id' => $id]);
                return $this->sendError('Forbidden: VIP contest.', ['error' => 'Forbidden'], 403);
            }

            if (now()->lt($contest->start_time) || now()->gt($contest->end_time)) {
                Log::info('Submit failed - contest inactive', ['contest_id' => $id]);
                return $this->sendError('Contest not active.', ['error' => 'Not active'], 400);
            }

            // entry
            $entry = UserContestEntry::firstOrCreate(
                ['user_id' => $user->id, 'contest_id' => $contest->id],
                ['status' => 'in_progress', 'score' => 0]
            );

            if ($entry->status === 'completed') {
                Log::info('Submit rejected - already completed', ['entry_id' => $entry->id]);
                return $this->sendError('You have already submitted this contest.', ['error' => 'Already submitted'], 409);
            }

            // prepare question map
            $questions = $contest->questions->keyBy('id');

            $totalScore = 0;
            $details = [];

            DB::beginTransaction();

            // remove previous answers (fresh submission)
            DB::table('user_answers')->where('entry_id', $entry->id)->delete();

            foreach ($request->answers as $ans) {
                $qId = $ans['question_id'];
                $selected = array_values(array_unique($ans['selected_option_ids']));

                if (! isset($questions[$qId])) {
                    $details[] = [
                        'question_id' => $qId,
                        'correct' => false,
                        'points_awarded' => 0,
                        'reason' => 'Invalid question for this contest'
                    ];
                    continue;
                }

                $question = $questions[$qId];
                $points = $question->points ?? 1;

                $correctOptionIds = $question->options->filter(fn($o) => $o->is_correct)
                    ->pluck('id')->sort()->values()->toArray();

                $selectedSorted = collect($selected)->sort()->values()->toArray();

                $isCorrect = false;
                if ($question->type === 'multi') {
                    $isCorrect = ($selectedSorted === $correctOptionIds);
                } else {
                    // single or boolean
                    $isCorrect = count($selectedSorted) > 0 && in_array($selectedSorted[0], $correctOptionIds);
                }

                // store per selected option (if option belongs to question)
                foreach ($selectedSorted as $optId) {
                    $opt = QuestionOption::where('id', $optId)->where('question_id', $qId)->first();
                    if ($opt) {
                        UserAnswer::create([
                            'entry_id' => $entry->id,
                            'question_id' => $qId,
                            'option_id' => $optId
                        ]);
                    }
                }

                $awarded = $isCorrect ? $points : 0;
                $totalScore += $awarded;

                $details[] = [
                    'question_id' => $qId,
                    'correct' => $isCorrect,
                    'points_awarded' => $awarded
                ];
            }

            // update entry
            $entry->score = $totalScore;
            $entry->status = 'completed';
            $entry->submitted_at = now();
            $entry->save();

            // update leaderboard table (simple upsert)
            DB::table('contest_leaderboards')->updateOrInsert(
                ['contest_id' => $contest->id, 'user_id' => $user->id],
                ['score' => $totalScore, 'updated_at' => now()]
            );

            DB::commit();

            return $this->sendResponse([
                'contest_id' => $contest->id,
                'user_id' => $user->id,
                'score' => $totalScore,
                'details' => $details
            ], 'Submission successful.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Submit error', ['error' => $e->getMessage(), 'contest_id' => $id]);
            return $this->sendError('Submission failed.', $e->getMessage(), 500);
        }
    }
}
