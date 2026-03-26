<?php

namespace App\Containers\Finance\FixedAsset\UI\API\Controllers;

use App\Containers\Finance\FixedAsset\Actions\CalculateDepreciationAction;
use App\Containers\Finance\FixedAsset\Actions\CreateFixedAssetAction;
use App\Containers\Finance\FixedAsset\Actions\ListFixedAssetsAction;
use App\Containers\Finance\FixedAsset\Actions\UpdateFixedAssetAction;
use App\Containers\Finance\FixedAsset\Models\FixedAsset;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FixedAssetController extends ApiController
{
    /**
     * List all fixed assets.
     */
    public function listAssets(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        $assets = app(ListFixedAssetsAction::class)->run($companyId);

        return response()->json([
            'data' => $assets,
        ]);
    }

    /**
     * Create a new fixed asset card.
     */
    public function createAsset(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        
        $data = $request->validate([
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:100',
            'category_code' => 'required|string|max:50',
            'purchase_date' => 'required|date',
            'start_use_date' => 'nullable|date',
            'original_value' => 'required|numeric|min:0',
            'useful_life_months' => 'required|integer|min:0',
            'residual_rate' => 'required|numeric|min:0|max:1',
            'depreciation_method' => 'nullable|string|in:straight_line,double_declining',
            'accumulated_depreciation' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
        ]);

        $data['company_id'] = $companyId;
        $data['asset_no'] = $data['code'];
        $data['category'] = $data['category_code'];
        $data['depreciation_method'] = $data['depreciation_method'] ?? 'straight_line';
        $data['accumulated_depreciation'] = $data['accumulated_depreciation'] ?? 0;
        $data['net_value'] = $data['original_value'] - $data['accumulated_depreciation'];
        $data['status'] = $data['status'] ?? 'active';

        $asset = app(CreateFixedAssetAction::class)->run($data);

        return response()->json([
            'data' => $asset,
        ], 201);
    }

    /**
     * Update fixed asset.
     */
    public function updateAsset(Request $request, $id): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        $data = $request->all();
        
        if (isset($data['code'])) $data['asset_no'] = $data['code'];
        if (isset($data['category_code'])) $data['category'] = $data['category_code'];

        $asset = app(UpdateFixedAssetAction::class)->run((int)$id, $data);

        return response()->json(['data' => $asset]);
    }

    /**
     * Delete fixed asset.
     */
    public function deleteAsset(Request $request, $id): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        $asset = FixedAsset::where('company_id', $companyId)->findOrFail($id);
        $asset->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Calculate depreciation for a period (Year/Month).
     */
    public function calculateDepreciation(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        
        $data = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $period = \App\Containers\Finance\Foundation\Models\Period::where('company_id', $companyId)
            ->where('year', $data['year'])
            ->where('month', $data['month'])
            ->first();

        if (!$period) {
            return response()->json(['message' => '会計期間が見つかりません (Period not found)'], 404);
        }

        $results = app(CalculateDepreciationAction::class)->run($companyId, $period->id);

        $preview = array_map(function($r) {
            return [
                'asset_code' => $r['asset_id'],
                'asset_name' => $r['asset_name'],
                'department' => 'Default',
                'amount' => $r['amount'],
            ];
        }, $results);

        return response()->json([
            'message' => '折旧测算完成',
            'preview' => $preview,
            'total_amount' => array_sum(array_column($results, 'amount')),
        ]);
    }

    /**
     * Generate voucher for depreciation.
     */
    public function generateVoucher(Request $request): JsonResponse
    {
        // Placeholder for now, will implement proper voucher generation logic later
        return response()->json(['message' => '凭证已自动生成(模拟)']);
    }
}
