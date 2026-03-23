<?php

namespace App\Containers\Finance\Auth\Models;

use App\Ship\Parents\Models\Model;

class Company extends Model
{
    protected $fillable = [
        'code',
        'name',
        'fiscal_year_start',
        'status',
    ];

    protected $casts = [
        'fiscal_year_start' => 'integer',
    ];
}
