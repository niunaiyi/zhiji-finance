<?php

namespace App\Containers\Finance\Payroll\Models;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'period_id',
        'payroll_no',
        'payroll_date',
        'status',
    ];

    protected $casts = [
        'payroll_date' => 'date',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollLine::class);
    }
}
