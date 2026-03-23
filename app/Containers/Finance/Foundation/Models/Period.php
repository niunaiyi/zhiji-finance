<?php

namespace App\Containers\Finance\Foundation\Models;

use App\Containers\AppSection\User\Models\User;
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
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'period_number' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    protected static function booted(): void
    {
        static::updating(function (Period $period) {
            if ($period->isDirty('status')) {
                $oldStatus = $period->getOriginal('status');
                $newStatus = $period->status;

                // locked periods cannot change status
                if ($oldStatus === 'locked') {
                    throw ValidationException::withMessages([
                        'status' => 'Locked periods cannot be modified',
                    ]);
                }

                // validate allowed transitions
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
