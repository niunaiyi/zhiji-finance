<?php

namespace App\Containers\Finance\FixedAsset\Actions;

use App\Containers\Finance\FixedAsset\Models\FixedAsset;
use App\Ship\Parents\Actions\Action;

/**
 * 创建固定资产卡片动作。
 * 负责资产档案的录入，包括资产类别、原值、存放地点等核心信息。
 */
class CreateFixedAssetAction extends Action
{
    public function run(array $data): FixedAsset
    {
        $this->checkRole(['admin', 'accountant']);

        return FixedAsset::create($data);
    }
}
