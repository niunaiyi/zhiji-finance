<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Data\Repositories\AccountRepository;
use App\Containers\Finance\Foundation\Tasks\AttachAuxCategoryToAccountTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Validation\ValidationException;

/**
 * 为会计科目关联辅助核算类别。
 * 验证科目是否支持辅助核算，并执行关联逻辑。
 */
class AttachAuxCategoryToAccountAction extends Action
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly AttachAuxCategoryToAccountTask $attachAuxCategoryToAccountTask,
    ) {}

    /**
     * 执行关联逻辑。
     *
     * @param array $data 包含 account_id, aux_category_id, is_required, sort_order
     * @return bool
     * @throws ValidationException 如果科目不支持辅助核算
     */
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
            $account,
            $data['aux_category_id'],
            $pivotData
        );
    }
}
