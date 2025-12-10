<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthenticationResource;
use App\Http\Resources\ContestsResource;
use App\Models\Contest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContestController extends BaseController
{
    /**
     * Get Contests api for Admin, VIP, Normal User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        try {
            // Admin & VIP see all contests, Normal see only normal
            if ($user->hasRole(['Admin', 'VIP'])) {
                $contests = Contest::orderBy('start_time', 'desc')->get();
            } else {
                $contests = Contest::where('access_level', 'normal')->orderBy('start_time', 'desc')->get();
            }

            Log::info('Contests fetched successfully.', ['List' => 'success']);
            return $this->sendResponse(new ContestsResource($contests), 'Contests fetched successfully.');
        } catch (Exception $e) {
            Log::info('Contest list error', ['error' => $e->getMessage()]);
            return $this->sendError('Unable to fetch contests.', $e->getMessage(), 500);
        }
    }

    /**
     * Get Contest Details api for Admin, VIP, Normal User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        try {
            $contest = Contest::with(['questions.options'])->find($id);

            if (! $contest) {
                Log::info('Contest not found', ['id' => $id]);
                return $this->sendError('Contest not found.', ['error' => 'Not found'], 404);
            }

            // Access control for VIP contests
            if ($contest->access_level === 'vip' && ! $user->hasRole(['VIP','Admin'])) {
                Log::info('Forbidden access to vip contest', ['user_id' => $user->id, 'contest_id' => $id]);
                return $this->sendError('Forbidden: VIP contest.', ['error' => 'Forbidden'], 403);
            }

            // Remove is_correct from options before returning
            $questions = $contest->questions->map(function ($q) {
                return [
                    'uid' => $q->id,
                    'question_text' => $q->question_text,
                    'type' => $q->type,
                    'points' => $q->points ?? 1,
                    'options' => $q->options->map(function ($opt) {
                        return [
                            'uid' => $opt->id,
                            'option_text' => $opt->option_text
                        ];
                    })
                ];
            });

            $payload = [
                'contest' => new ContestsResource($contest),
                'questions' => $questions
            ];

            return $this->sendResponse($payload, 'Contest details retrieved.');
        } catch (Exception $e) {
            Log::info('Contest show error', ['error' => $e->getMessage(), 'id' => $id]);
            return $this->sendError('Unable to retrieve contest.', $e->getMessage(), 500);
        }
    }
}
