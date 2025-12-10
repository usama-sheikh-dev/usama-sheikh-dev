<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\UserContestEntry;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserHistoryController extends Controller
{
    // GET /user/history
    public function index(Request $request)
    {
        $user = $request->user();

        try {
            $entries = UserContestEntry::with('contest')
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($e) {
                    return [
                        'contest_id' => $e->contest_id,
                        'contest_name' => optional($e->contest)->name,
                        'status' => $e->status,
                        'score' => $e->score,
                        'joined_at' => $e->created_at,
                        'submitted_at' => $e->submitted_at
                    ];
                });

            $prizes = $user->prizes()->with('contest')->get()->map(function ($p) {
                return [
                    'contest_id' => $p->contest_id,
                    'contest_name' => optional($p->contest)->name,
                    'prize' => $p->prize,
                    'awarded_at' => $p->created_at
                ];
            });

            return $this->sendResponse(['entries' => $entries, 'prizes' => $prizes], 'User history retrieved.');
        } catch (Exception $e) {
            Log::info('User history error', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return $this->sendError('Unable to fetch user history.', $e->getMessage(), 500);
        }
    }
}
