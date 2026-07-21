<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Events\Models\Event;
use Modules\Events\Models\Member;
use Modules\Users\Models\Feedback;
use Modules\Users\Models\Filter;
use Modules\Users\Models\Profile;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'fcm_token',
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

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

    public function filter(): HasOne
    {
        return $this->hasOne(Filter::class, 'user_id', 'id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class, 'user_id', 'id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'user_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        self::created(function (User $user) {
            $user->profile()->create(['name' => $user->name]);
            $user->filter()->create();
        });
    }

    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
}
