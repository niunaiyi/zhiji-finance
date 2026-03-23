<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Validation\ValidationException;

class ClosePeriodTask extends Task
{
    public function run(Period $period): Period
    {
        // Validation is handled by Period model's updating event
        $period->status = 'closed';
        $period->save(); // Model will validate and auto-set closed_at/closed_by

        return $period->fresh();
    }
}
