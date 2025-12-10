<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\UserContestEntry;
use App\Models\UserPrize;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminContestController extends Controller
{
    // POST /contests  (admin only)
    public function store(Request $request)
    {
        $user = $request->user();

        // role check
        if (! $user->hasRole(['Admin'])) {
            Log::info('Create contest forbidden - not admin', ['user_id' => $user->id]);
            return $this->sendError('Unauthorized.', ['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'access_level' => 'required|in:vip,normal',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'prize' => 'nullable|string|max:255',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.type' => 'required_with:questions|in:single,multi,boolean',
            'questions.*.points' => 'nullable|integer|min:0',
            'questions.*.options' => 'required_with:questions|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            Log::info('Create contest validation error', ['errors' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $contest = Contest::create([
                'name' => $request->name,
                'description' => $request->description,
                'access_level' => $request->access_level,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'prize' => $request->prize,
            ]);

            // create questions & options if provided
            if ($request->filled('questions')) {
                foreach ($request->questions as $q) {
                    $question = Question::create([
                        'contest_id' => $contest->id,
                        'question_text' => $q['question_text'],
                        'type' => $q['type'],
                        'points' => $q['points'] ?? 1
                    ]);

                    foreach ($q['options'] as $opt) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $opt['option_text'],
                            'is_correct' => $opt['is_correct']
                        ]);
                    }
                }
            }

            DB::commit();
            return $this->sendResponse($contest, 'Contest created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Create contest error', ['error' => $e->getMessage()]);
            return $this->sendError('Unable to create contest.', $e->getMessage(), 500);
        }
    }

    // PUT /contests/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (! $user->hasRole(['Admin'])) {
            Log::info('Update contest forbidden - not admin', ['user_id' => $user->id]);
            return $this->sendError('Unauthorized.', ['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'access_level' => 'nullable|in:vip,normal',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'prize' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::info('Update contest validation error', ['errors' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }

        try {
            $contest = Contest::find($id);
            if (! $contest) {
                Log::info('Update contest not found', ['contest_id' => $id]);
                return $this->sendError('Contest not found.', ['error' => 'Not found'], 404);
            }

            $contest->update($request->only(['name','description','access_level','start_time','end_time','prize']));

            return $this->sendResponse($contest, 'Contest updated successfully.');
        } catch (Exception $e) {
            Log::info('Update contest error', ['error' => $e->getMessage(), 'contest_id' => $id]);
            return $this->sendError('Unable to update contest.', $e->getMessage(), 500);
        }
    }

    // DELETE /contests/{id}
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (! $user->hasRole(['Admin'])) {
            Log::info('Delete contest forbidden - not admin', ['user_id' => $user->id]);
            return $this->sendError('Unauthorized.', ['error' => 'Unauthorized'], 403);
        }

        try {
            $contest = Contest::find($id);
            if (! $contest) {
                Log::info('Delete contest not found', ['contest_id' => $id]);
                return $this->sendError('Contest not found.', ['error' => 'Not found'], 404);
            }

            $contest->delete();
            return $this->sendResponse(null, 'Contest deleted successfully.');
        } catch (Exception $e) {
            Log::info('Delete contest error', ['error' => $e->getMessage(), 'contest_id' => $id]);
            return $this->sendError('Unable to delete contest.', $e->getMessage(), 500);
        }
    }

    // POST /contests/{id}/declare-winner  (manual assignment or auto-calc helper)
    // Payload: { user_id: int, prize: string }  OR call without user_id to auto-calc top 1
    public function declareWinner(Request $request, $id)
    {
        $user = $request->user();

        if (! $user->hasRole(['Admin'])) {
            Log::info('Declare winner forbidden - not admin', ['user_id' => $user->id]);
            return $this->sendError('Unauthorized.', ['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|integer|exists:users,id',
            'prize' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            Log::info('Declare winner validation error', ['errors' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }

        try {
            $contest = Contest::find($id);
            if (! $contest) {
                Log::info('Declare winner - contest not found', ['contest_id' => $id]);
                return $this->sendError('Contest not found.', ['error' => 'Not found'], 404);
            }

            // manual assignment
            if ($request->filled('user_id')) {
                $winnerUserId = $request->user_id;
            } else {
                // auto-calc: top score from user_contest_entries
                $top = UserContestEntry::where('contest_id', $contest->id)
                    ->where('status', 'completed')
                    ->orderByDesc('score')
                    ->orderBy('submitted_at')
                    ->first();

                if (! $top) {
                    return $this->sendError('No completed submissions to determine winner.', ['error' => 'No submissions'], 400);
                }
                $winnerUserId = $top->user_id;
            }

            // award prize
            $prizeText = $request->prize ?? $contest->prize ?? 'Prize';
            $prize = UserPrize::create([
                'user_id' => $winnerUserId,
                'contest_id' => $contest->id,
                'prize' => $prizeText
            ]);

            // update contest_leaderboards table rank (optional)
            // here we simply ensure the winner exists in leaderboard
            \DB::table('contest_leaderboards')->updateOrInsert(
                ['contest_id' => $contest->id, 'user_id' => $winnerUserId],
                ['score' => UserContestEntry::where('contest_id', $contest->id)->where('user_id', $winnerUserId)->value('score') ?? 0, 'updated_at' => now()]
            );

            return $this->sendResponse($prize, 'Winner declared and prize awarded.');
        } catch (Exception $e) {
            Log::info('Declare winner error', ['error' => $e->getMessage(), 'contest_id' => $id]);
            return $this->sendError('Unable to declare winner.', $e->getMessage(), 500);
        }
    }
}
