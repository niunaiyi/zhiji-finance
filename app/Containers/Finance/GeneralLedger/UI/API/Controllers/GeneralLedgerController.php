<?php

namespace App\Containers\Finance\GeneralLedger\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\GeneralLedger\Actions\GetAuxiliaryLedgerAction;
use App\Containers\Finance\GeneralLedger\Actions\GetBalanceSheetAction;
use App\Containers\Finance\GeneralLedger\Actions\GetChronologicalLedgerAction;
use App\Containers\Finance\GeneralLedger\Actions\GetDetailLedgerAction;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneralLedgerController extends ApiController
{
    public function balanceSheet(Request $request): JsonResponse
    {
        $companyId = app('current.company_id');
        $periodId = $request->input('period_id');
        $accountId = $request->input('account_id');

        $data = app(GetBalanceSheetAction::class)->run($companyId, $periodId, $accountId);

        return Response::create($data)->ok();
    }

    public function detailLedger(Request $request): JsonResponse
    {
        $companyId = app('current.company_id');
        $periodId = $request->input('period_id');
        $accountId = $request->input('account_id');

        $data = app(GetDetailLedgerAction::class)->run($companyId, $periodId, $accountId);

        return Response::create($data)->ok();
    }

    public function chronological(Request $request): JsonResponse
    {
        $companyId = app('current.company_id');
        $periodId = $request->input('period_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = app(GetChronologicalLedgerAction::class)->run($companyId, $periodId, $startDate, $endDate);

        return Response::create($data)->ok();
    }

    public function auxiliaryLedger(Request $request): JsonResponse
    {
        $companyId = app('current.company_id');
        $periodId = $request->input('period_id');
        $auxCategoryId = $request->input('aux_category_id');
        $auxItemId = $request->input('aux_item_id');

        $data = app(GetAuxiliaryLedgerAction::class)->run($companyId, $periodId, $auxCategoryId, $auxItemId);

        return Response::create($data)->ok();
    }
}
