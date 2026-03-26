<?php

namespace App\Containers\Finance\Payroll\Models;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Models\Model;
use App\Ship\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'payroll_id',
        'employee_id',
        'dept_id',
        'total_earning',
        'total_deduction',
        'net_pay',
    ];

    protected $casts = [
        'total_earning' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AuxItem::class, 'employee_id');
    }
}
