<?php

namespace App\Containers\Finance\FixedAsset\Models;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationSchedule extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'fixed_asset_id',
        'period_id',
        'depreciation_amount',
        'is_posted',
    ];

    protected $casts = [
        'depreciation_amount' => 'decimal:2',
        'is_posted' => 'boolean',
    ];

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
}
