<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;

class CalculateAccountLevelTask extends Task
{
    public function run(?int $parentId, string $code): array
    {
        $level = strlen($code) / 4;

        if ($parentId) {
            $parent = Account::findOrFail($parentId);
            return [
                'level' => $level,
                'element_type' => $parent->element_type,
                'balance_direction' => $parent->balance_direction,
            ];
        }

        return ['level' => $level];
    }
}
