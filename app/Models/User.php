<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method static create(array $user)
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getUserPic(): string
    {
        if($this->picture !== null) {
            return asset('images/profile/'.$this->picture);
        }else{
            return asset('images/no-image.png');
        }
    }

    public function contestEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserContestEntry::class);
    }

    public function leaderboards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ContestLeaderboard::class);
    }

    public function prizes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserPrize::class);
    }
}
