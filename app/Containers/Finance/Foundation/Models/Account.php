<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'level',
        'element_type',
        'balance_direction',
        'is_detail',
        'is_active',
        'has_aux',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_detail' => 'boolean',
        'is_active' => 'boolean',
        'has_aux' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function auxCategories(): BelongsToMany
    {
        return $this->belongsToMany(AuxCategory::class, 'account_aux_categories')
            ->withPivot(['is_required', 'sort_order'])
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        // Auto-update parent's is_detail when child is created
        static::created(function (Account $account) {
            if ($account->parent_id) {
                Account::withoutGlobalScopes()
                    ->where('id', $account->parent_id)
                    ->update(['is_detail' => false]);
            }
        });
    }
}
