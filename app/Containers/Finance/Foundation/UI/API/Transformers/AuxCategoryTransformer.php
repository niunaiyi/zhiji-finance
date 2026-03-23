<?php

namespace App\Containers\Finance\Foundation\UI\API\Transformers;

use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Transformers\Transformer;

class AuxCategoryTransformer extends Transformer
{
    protected array $defaultIncludes = [];
    protected array $availableIncludes = [];

    public function transform(AuxCategory $auxCategory): array
    {
        return [
            'id' => $auxCategory->id,
            'code' => $auxCategory->code,
            'name' => $auxCategory->name,
            'is_system' => $auxCategory->is_system,
            'created_at' => $auxCategory->created_at?->toIso8601String(),
        ];
    }
}
