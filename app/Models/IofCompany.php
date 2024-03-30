<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IofCompany extends Model
{
    /**
     * Default database connection (mysql)
     * When used multiple database in same application
     */
    protected $connection = 'mysql';
    protected $table = 'iofcompanies';

    protected $guarded = [];

    protected $attributes = [
        'status' => 1, //Default active selected value
        'type' => 1 //Default active selected value
    ];

    public function getStatusAttribute($attribute): string
    {
        return $this->statusOption()[$attribute];
    }

    public function statusOption(): array
    {
        return [
            '0' => 'Invisible',
            '1' => 'Visible',
        ];
    }

    public function getTypeAttribute($attribute): string
    {
        return $this->typeOptions()[$attribute];
    }

    public function typeOptions(): array
    {
        return [
            '1' => 'IGW',
            '2' => 'IOS',
            '3' => 'ICX',
            '4' => 'ANS'
        ];
    }

    public function scopeIgwCompany($query){
        return $query->where('type', 1);
    }

    public function scopeIosCompany($query){
        return $query->where('type', 2);
    }

    public function scopeIcxCompany($query){
        return $query->where('type', 3);
    }

    public function scopeAnsCompany($query){
        return $query->where('type', 4);
    }
}
