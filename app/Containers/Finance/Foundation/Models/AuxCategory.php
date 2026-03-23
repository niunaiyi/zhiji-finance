<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

class AuxCategory extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'code',
        'name',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];
}
