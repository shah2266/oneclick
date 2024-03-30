<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NoclickCommand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'command', 'mail_template_id', 'status', 'user_id'];

    // Default active value
    protected $attributes = [
        'status' => 'on'
    ];


    public function noclickMailTemplate(): HasOne
    {
        return $this->hasOne(NoclickMailTemplate::class,'id', 'mail_template_id');
    }

    public function noclickSchedule(): BelongsTo
    {
        return $this->belongsTo(NoclickSchedule::class,'id', 'command_id');
    }

    public function getStatusAttribute($attribute):string
    {
        return $attribute ?? $this->attributes['status'];
    }

    public function statusOptions(): array
    {
        return [
            'on' => 'on',
            'off' => 'off'
        ];
    }

    public function scopeActive($query) {
        return $query->where('status', 'on');
    }
}
