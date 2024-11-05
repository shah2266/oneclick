<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $guarded=[];

    protected $attributes = [
        'status'=> 'active',
        'environment'=> 'local',
    ];

    public function getStatusAttribute($attribute): string
    {
        return $this->statusOptions()[$attribute];
    }

    public function statusOptions(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    public function getEnvironmentAttribute($attribute): string
    {
        return $this->environmentOptions()[$attribute];
    }

    public function environmentOptions(): array
    {
        return [
            'local' => 'Local',
            'production' => 'Production',
        ];
    }
}
