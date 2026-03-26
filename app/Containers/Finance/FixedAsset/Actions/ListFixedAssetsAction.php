<?php

namespace App\Containers\Finance\FixedAsset\Actions;

use App\Containers\Finance\FixedAsset\Models\FixedAsset;
use App\Ship\Parents\Actions\Action;
use Illuminate\Database\Eloquent\Collection;

class ListFixedAssetsAction extends Action
{
    public function run(int $companyId): Collection
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        return FixedAsset::where('company_id', $companyId)->get();
    }
}
