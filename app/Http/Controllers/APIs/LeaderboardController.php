<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\UserContestEntry;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaderboardController extends BaseController
{
    // Simple leaderboard: ranks by highest score, tiebreaker by earliest submitted_at
    // GET /contests/{id}/leaderboard
    public function index(Request $request, $contestId)
    {
        try {
            $contest = Contest::find($contestId);
            if (! $contest) {
                Log::info('Leaderboard contest not found', ['contest_id' => $contestId]);
                return $this->sendError('Contest not found.', ['error' => 'Not found'], 404);
            }

            $entries = UserContestEntry::where('contest_id', $contestId)
                ->where('status', 'completed')
                ->with('user:id,name')
                ->orderByDesc('score')
                ->orderBy('submitted_at')
                ->get();

            $rank = 0;
            $lastScore = null;
            $results = [];
            foreach ($entries as $entry) {
                if ($lastScore === null || $entry->score < $lastScore) {
                    $rank++;
                    $lastScore = $entry->score;
                }
                $results[] = [
                    'rank' => $rank,
                    'user_id' => $entry->user->id,
                    'user_name' => $entry->user->name,
                    'score' => $entry->score,
                    'submitted_at' => $entry->submitted_at
                ];
            }

            return $this->sendResponse($results, 'Leaderboard retrieved.');
        } catch (Exception $e) {
            Log::info('Leaderboard error', ['error' => $e->getMessage(), 'contest_id' => $contestId]);
            return $this->sendError('Unable to fetch leaderboard.', $e->getMessage(), 500);
        }
    }
}
