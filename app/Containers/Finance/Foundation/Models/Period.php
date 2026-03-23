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
        // Use 'saving' event to catch all save attempts, including when status isn't dirty
        static::saving(function (Period $period) {
            // Skip validation for new records
            if (!$period->exists) {
                return;
            }

            // Locked periods are completely immutable
            if ($period->getOriginal('status') === 'locked' && $period->isDirty()) {
                throw ValidationException::withMessages([
                    'period' => 'Locked periods cannot be modified',
                ]);
            }

            // Validate status transitions (including same-status attempts)
            $currentStatus = $period->status;
            $originalStatus = $period->getOriginal('status');

            if ($currentStatus !== $originalStatus || !$period->isDirty('status')) {
                // Status has changed OR status is set but not dirty (same value)
                if ($currentStatus === $originalStatus) {
                    // Attempting to transition to the same status (e.g., closed→closed)
                    throw ValidationException::withMessages([
                        'status' => "Invalid status transition from {$originalStatus} to {$currentStatus}",
                    ]);
                }

                // Auto-set closed_at and closed_by when closing
                if ($currentStatus === 'closed' && $originalStatus !== 'closed') {
                    $period->closed_at = now();
                    $period->closed_by = auth()->id();
                }

                // Clear closed_at and closed_by when reopening
                if ($currentStatus === 'open' && $originalStatus === 'closed') {
                    $period->closed_at = null;
                    $period->closed_by = null;
                }

                // Validate allowed transitions
                $allowedTransitions = [
                    'open' => ['closed'],
                    'closed' => ['open', 'locked'],
                ];

                if (!isset($allowedTransitions[$originalStatus]) ||
                    !in_array($currentStatus, $allowedTransitions[$originalStatus])) {
                    throw ValidationException::withMessages([
                        'status' => "Invalid status transition from {$originalStatus} to {$currentStatus}",
                    ]);
                }
            }
        });
    }
}
