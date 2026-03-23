<?php

namespace App\Containers\Finance\Foundation\UI\API\Transformers;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Transformers\Transformer;

class AuxItemTransformer extends Transformer
{
    protected array $defaultIncludes = [];
    protected array $availableIncludes = ['auxCategory', 'parent', 'children'];

    public function transform(AuxItem $auxItem): array
    {
        return [
            'id' => $auxItem->id,
            'aux_category_id' => $auxItem->aux_category_id,
            'code' => $auxItem->code,
            'name' => $auxItem->name,
            'parent_id' => $auxItem->parent_id,
            'is_active' => $auxItem->is_active,
            'extra' => $auxItem->extra,
            'created_at' => $auxItem->created_at?->toIso8601String(),
        ];
    }

    public function includeAuxCategory(AuxItem $auxItem): ?\League\Fractal\Resource\Item
    {
        return $auxItem->auxCategory ? $this->item($auxItem->auxCategory, new AuxCategoryTransformer()) : null;
    }

    public function includeParent(AuxItem $auxItem): ?\League\Fractal\Resource\Item
    {
        return $auxItem->parent ? $this->item($auxItem->parent, new self()) : null;
    }

    public function includeChildren(AuxItem $auxItem): \League\Fractal\Resource\Collection
    {
        return $this->collection($auxItem->children, new self());
    }
}
