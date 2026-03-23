<?php

namespace App\Containers\Finance\Foundation\UI\API\Transformers;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Transformers\Transformer;

class AccountTransformer extends Transformer
{
    protected array $defaultIncludes = [];
    protected array $availableIncludes = ['parent', 'children'];

    public function transform(Account $account): array
    {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'parent_id' => $account->parent_id,
            'level' => $account->level,
            'element_type' => $account->element_type,
            'balance_direction' => $account->balance_direction,
            'is_detail' => $account->is_detail,
            'is_active' => $account->is_active,
            'has_aux' => $account->has_aux,
            'created_at' => $account->created_at?->toIso8601String(),
        ];
    }

    public function includeParent(Account $account): ?\League\Fractal\Resource\Item
    {
        return $account->parent ? $this->item($account->parent, new self()) : null;
    }

    public function includeChildren(Account $account): \League\Fractal\Resource\Collection
    {
        return $this->collection($account->children, new self());
    }
}
