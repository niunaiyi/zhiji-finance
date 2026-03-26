<?php

namespace App\Containers\Finance\Inventory\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;

class InventoryItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'sku',
        'name',
        'unit',
        'category',
        'current_quantity',
        'current_average_cost',
        'is_active',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:4',
        'current_average_cost' => 'decimal:4',
        'is_active' => 'boolean',
    ];
}
