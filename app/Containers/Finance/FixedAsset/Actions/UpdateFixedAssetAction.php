<?php

namespace App\Containers\Finance\FixedAsset\Actions;

use App\Containers\Finance\FixedAsset\Models\FixedAsset;
use App\Ship\Parents\Actions\Action;

class UpdateFixedAssetAction extends Action
{
    public function run(int $id, array $data): FixedAsset
    {
        $this->checkRole(['admin', 'accountant']);

        $asset = FixedAsset::findOrFail($id);
        $asset->update($data);
        
        // 重新计算净值
        $asset->update(['net_value' => $asset->original_value - $asset->accumulated_depreciation]);

        return $asset;
    }
}
