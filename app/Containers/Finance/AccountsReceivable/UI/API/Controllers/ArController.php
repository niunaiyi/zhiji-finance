<?php

namespace App\Containers\Finance\AccountsReceivable\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\AccountsReceivable\Actions\CreateArBillAction;
use App\Containers\Finance\AccountsReceivable\Actions\CreateArReceiptAction;
use App\Containers\Finance\AccountsReceivable\Actions\ListArBillsAction;
use App\Containers\Finance\AccountsReceivable\Actions\ListArReceiptsAction;
use App\Containers\Finance\AccountsReceivable\Actions\SettleArAction;
use App\Containers\Finance\AccountsReceivable\Models\ArBill;
use App\Containers\Finance\AccountsReceivable\Models\ArReceipt;
use App\Containers\Finance\AccountsReceivable\UI\API\Transformers\ArBillTransformer;
use App\Containers\Finance\AccountsReceivable\UI\API\Transformers\ArReceiptTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\Request;

/**
 * 应收账款模块 API 控制器。
 * 处理应收单据、收款单的创建、查询及自动/手动核销业务。
 */
class ArController extends ApiController
{
    // ─── 应收单据 (AR Bills) ──────────────────────────────────────────────────────────

    public function indexBills(Request $request)
    {
        $bills = app(ListArBillsAction::class)->run($request->all());
        return Response::create($bills, ArBillTransformer::class)->ok();
    }

    public function storeBill(Request $request)
    {
        $data = $request->validate([
            'period_id'   => 'required|integer',
            'customer_id' => 'required|integer',
            'bill_no'     => 'required|string|max:30',
            'bill_date'   => 'required|date',
            'amount'      => 'required|numeric|min:0.01',
            'source_type' => 'nullable|string',
            'source_id'   => 'nullable|integer',
        ]);

        $bill = app(CreateArBillAction::class)->run($data);
        return Response::create($bill, ArBillTransformer::class)->created();
    }

    public function showBill(int $id)
    {
        $bill = ArBill::with(['customer', 'period', 'settlements'])->findOrFail($id);
        return Response::create($bill, ArBillTransformer::class)->ok();
    }

    // ─── 收款单 (AR Receipts) ───────────────────────────────────────────────────────

    public function indexReceipts(Request $request)
    {
        $receipts = app(ListArReceiptsAction::class)->run($request->all());
        return Response::create($receipts, ArReceiptTransformer::class)->ok();
    }

    public function storeReceipt(Request $request)
    {
        $data = $request->validate([
            'period_id'    => 'required|integer',
            'customer_id'  => 'required|integer',
            'receipt_no'   => 'required|string|max:30',
            'receipt_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
        ]);

        $receipt = app(CreateArReceiptAction::class)->run($data);
        return Response::create($receipt, ArReceiptTransformer::class)->created();
    }

    public function showReceipt(int $id)
    {
        $receipt = ArReceipt::with(['customer', 'period', 'settlements'])->findOrFail($id);
        return Response::create($receipt, ArReceiptTransformer::class)->ok();
    }

    // ─── 核销业务 (Settlement) ──────────────────────────────────────────────────

    public function settle(Request $request)
    {
        $data = $request->validate([
            'ar_bill_id'    => 'required|integer',
            'ar_receipt_id' => 'required|integer',
            'amount'        => 'required|numeric|min:0.01',
        ]);

        try {
            app(SettleArAction::class)->run($data);
            return response()->json(['message' => 'Settled successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
