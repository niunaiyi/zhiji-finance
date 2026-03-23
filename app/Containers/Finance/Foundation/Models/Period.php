<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Period extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'fiscal_year',
        'period_number',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'period_number' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    protected static function booted(): void
    {
        static::updating(function (Period $period) {
            // Locked periods are completely immutable
            if ($period->getOriginal('status') === 'locked' && $period->isDirty()) {
                throw ValidationException::withMessages([
                    'period' => 'Locked periods cannot be modified',
                ]);
            }

            if ($period->isDirty('status')) {
                $oldStatus = $period->getOriginal('status');
                $newStatus = $period->status;

                // Auto-set closed_at and closed_by when closing
                if ($newStatus === 'closed' && $oldStatus !== 'closed') {
                    $period->closed_at = now();
                    $period->closed_by = auth()->id();
                }

                // Clear closed_at and closed_by when reopening
                if ($newStatus === 'open' && $oldStatus === 'closed') {
                    $period->closed_at = null;
                    $period->closed_by = null;
                }

                // Validate allowed transitions
                $allowedTransitions = [
                    'open' => ['closed'],
                    'closed' => ['open', 'locked'],
                ];

                if (!isset($allowedTransitions[$oldStatus]) ||
                    !in_array($newStatus, $allowedTransitions[$oldStatus])) {
                    throw ValidationException::withMessages([
                        'status' => "Invalid status transition from {$oldStatus} to {$newStatus}",
                    ]);
                }
            }
        });
    }
}
