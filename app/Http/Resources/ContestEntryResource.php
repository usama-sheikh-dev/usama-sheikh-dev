<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

/**
 * @method getUserPic()
 * @method getRoleNames()
 * @method getRoleName()
 * @property mixed $created_at
 */
class ContestEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = $this->user()?->name ?? null;
        $contest = $this->contest()?->name ?? null;
        $status =  $this->access_level ?? null;
        $score = $this->start_time ?? null;

        return [
            'user' => $user,
            'contest' => $contest,
            'status' => $status,
            'score' => $score,
        ];
    }
}
