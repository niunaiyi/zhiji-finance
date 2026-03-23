<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Tasks\InitializeFiscalYearTask;
use App\Ship\Parents\Actions\Action;

class InitializeFiscalYearAction extends Action
{
    public function __construct(
        private readonly InitializeFiscalYearTask $initializeFiscalYearTask,
    ) {}

    public function run(array $data): array
    {
        return $this->initializeFiscalYearTask->run($data);
    }
}
