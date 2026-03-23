<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Ship\Parents\Tasks\Task;
use Illuminate\Validation\ValidationException;

class ValidateAccountCodeTask extends Task
{
    public function run(string $code): void
    {
        // Validate 4-digit segments
        if (!preg_match('/^(\d{4})+$/', $code)) {
            throw ValidationException::withMessages([
                'code' => 'Account code must be 4-digit segments (e.g., 1001, 100101)'
            ]);
        }

        // Validate max 4 levels (16 digits)
        if (strlen($code) > 16) {
            throw ValidationException::withMessages([
                'code' => 'Account code cannot exceed 4 levels (16 digits)'
            ]);
        }
    }
}
