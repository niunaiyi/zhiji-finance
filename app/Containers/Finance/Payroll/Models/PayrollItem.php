<?php

namespace App\Containers\Finance\Payroll\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

class PayrollItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
