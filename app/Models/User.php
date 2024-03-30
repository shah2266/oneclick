<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'name',
        'email',
        'contact_number',
        'password',
        'user_type',
        'theme_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $attributes = [
        'user_type' => 0,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Dynamically set the default value for user_type
        $this->attributes['user_type'] = $this->calculateUserType();
    }

    /**
     * @return int
     */
    private function calculateUserType(): int
    {
        // Your dynamic logic to determine the user_type value
        // For example, you can fetch it from a configuration, database, or any other source
        $dynamicUserType = 1; // Replace this with dynamic logic

        // If user_type is 0, set the default value to 0
        return $this->attributes['user_type'] == 0 ? 0 : $dynamicUserType;
    }

    public function getUserTypeAttribute($attribute): int
    {
        return $attribute ?? $this->attributes['user_type'];
    }

    public function userTypes(): array
    {
        $userType = Auth::user()->user_type;

        if ($userType === 0) {
            $roles = [0 => 'Super admin', 1 => 'Admin', 2 => 'User'];
        } elseif ($userType === 1) {
            $roles = [1 => 'Admin', 2 => 'User'];
        } else {
            $roles = [2 => 'User'];
        }

        return $roles;
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(UserTheme::class, 'theme_id');
    }
}
