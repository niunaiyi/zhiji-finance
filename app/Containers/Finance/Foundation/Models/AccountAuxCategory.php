<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Ship\Parents\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountAuxCategory extends Model
{
    protected $fillable = [
        'account_id',
        'aux_category_id',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function auxCategory(): BelongsTo
    {
        return $this->belongsTo(AuxCategory::class);
    }
}
