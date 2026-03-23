<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Data\Repositories\AccountRepository;
use App\Containers\Finance\Foundation\Tasks\DetachAuxCategoryFromAccountTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Validation\ValidationException;

class DetachAuxCategoryFromAccountAction extends Action
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly DetachAuxCategoryFromAccountTask $detachAuxCategoryFromAccountTask,
    ) {}

    public function run(array $data): bool
    {
        $account = $this->accountRepository->find($data['account_id']);

        if (!$account->has_aux) {
            throw ValidationException::withMessages([
                'account_id' => ['Account does not support auxiliary accounting'],
            ]);
        }

        return $this->detachAuxCategoryFromAccountTask->run(
            $data['account_id'],
            $data['aux_category_id']
        );
    }
}
