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
class ContestsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $uid =  $this->id ?? null;
        $name = $this->name ?? null;
        $description = $this->description ?? null;
        $access_level =  $this->access_level ?? null;
        $start_time = $this->start_time ?? null;
        $end_time = $this->end_time ?? null;
        $prize = $this->prize ?? null;

        return [
            'uid' => $uid,
            'name' => $name,
            'description' => $description,
            'access_level' => $access_level,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'prize' => $prize,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
