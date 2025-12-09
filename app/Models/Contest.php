<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    protected $fillable = [
        'name', 'description', 'access_level', 'start_time', 'end_time', 'prize'
    ];

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function entries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserContestEntry::class);
    }

    public function leaderboard(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ContestLeaderboard::class);
    }

    public function prizes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserPrize::class);
    }
}
