<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_name',
        'stylesheet_name',
        'user_id',
        'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'theme_id');
    }

}
