<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Data\Repositories\AccountRepository;
use App\Containers\Finance\Foundation\Tasks\AttachAuxCategoryToAccountTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Validation\ValidationException;

class AttachAuxCategoryToAccountAction extends Action
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly AttachAuxCategoryToAccountTask $attachAuxCategoryToAccountTask,
    ) {}

    public function run(array $data): bool
    {
        $account = $this->accountRepository->find($data['account_id']);

        if (!$account->has_aux) {
            throw ValidationException::withMessages([
                'account_id' => ['Account does not support auxiliary accounting'],
            ]);
        }

        $pivotData = [
            'is_required' => $data['is_required'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ];

        return $this->attachAuxCategoryToAccountTask->run(
            $data['account_id'],
            $data['aux_category_id'],
            $pivotData
        );
    }
}
