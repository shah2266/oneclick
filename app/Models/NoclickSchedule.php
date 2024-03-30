<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static activeCommand()
 * @method static getHolidayCommand()
 */
class NoclickSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['frequency', 'command_id', 'days', 'time', 'holiday', 'status', 'user_id'];

    // Default active value
    protected $attributes = [
        'status' => 'on',
        'frequency' => 0
    ];

    public function noclickCommand(): HasOne
    {
        return $this->hasOne(NoclickCommand::class,'id', 'command_id');
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


    public function getNameAttribute($attribute):string
    {
        return $attribute ?? $this->attributes['frequency'];
    }

    public function frequencyOptions(): array
    {
        return [
            0 => 'Regular',
            1 => 'Monthly',
            2 => 'Holiday',
            3 => 'CDR status'
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'on');
    }

    public function scopeActiveCommand($query)
    {
        return $query->active()
            ->whereHas('noclickCommand', function ($query) {
                $query->active()->where('status', 'on');
            })
            ->where('status', 'on');
    }

    public function scopeNotNullHolidayValue($query)
    {
        return $query->whereNotNull('holiday')
            ->where('status', 'on')
            ->whereHas('noclickCommand', function ($query) {
                $query->active()->where('status', 'on');
            })
            ->exists();
    }

    public function scopeGetHolidayCommand($query)
    {
        return $query->whereNotNull('holiday')
            ->where('status', 'on')
            ->where('frequency', 2)
            ->whereHas('noclickCommand', function ($query) {
                $query->active()->where('status', 'on');
            });
    }

}
