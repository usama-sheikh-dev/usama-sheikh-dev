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
class AuthenticationResource extends JsonResource
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
        $email = $this->email ?? null;
        $role_name =  $this->getRoleNames()->first() ?? null;
        $pitcher = $this->getUserPic() ?? null;
        $status = $this->status ?? null;
        $token = $this->token ?? null;

        return [
            'uid' => $uid,
            'name' => $name,
            'email' => $email,
            'picture' => $pitcher,
            'role' => $role_name,
            'status' => $status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'token' => $token,
        ];
    }
}
