<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServerList extends Model
{
    use HasFactory;

    /**
     * Default database connection (mysql)
     * When used multiple database in same application
     */
    protected $connection = 'mysql';

    protected $guarded = [];

    protected $attributes = [
        'status' => 1, //Default active selected value
    ];

    public function getStatusAttribute($attribute): string
    {
        return $this->statusOption()[$attribute];
    }

    public function statusOption(): array
    {
        return [
            '0' => 'Inactive',
            '1' => 'Active',
        ];
    }
}
