<?php

namespace App\Containers\Finance\AccountsPayable\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\AccountsPayable\Actions\CreateApBillAction;
use App\Containers\Finance\AccountsPayable\Actions\CreateApPaymentAction;
use App\Containers\Finance\AccountsPayable\Actions\ListApBillsAction;
use App\Containers\Finance\AccountsPayable\Actions\ListApPaymentsAction;
use App\Containers\Finance\AccountsPayable\Actions\SettleApAction;
use App\Containers\Finance\AccountsPayable\Models\ApBill;
use App\Containers\Finance\AccountsPayable\Models\ApPayment;
use App\Containers\Finance\AccountsPayable\UI\API\Transformers\ApBillTransformer;
use App\Containers\Finance\AccountsPayable\UI\API\Transformers\ApPaymentTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\Request;

/**
 * 应付账款模块 API 控制器。
 * 处理应付单据、付款单的记录及其核销流程。
 */
class ApController extends ApiController
{
    // ─── 应付单据 (AP Bills) ──────────────────────────────────────────────────────────

    public function indexBills(Request $request)
    {
        $bills = app(ListApBillsAction::class)->run($request->all());
        return Response::create($bills, ApBillTransformer::class)->ok();
    }

    public function storeBill(Request $request)
    {
        $data = $request->validate([
            'period_id'   => 'required|integer',
            'supplier_id' => 'required|integer',
            'bill_no'     => 'required|string|max:30',
            'bill_date'   => 'required|date',
            'amount'      => 'required|numeric|min:0.01',
            'is_estimate' => 'boolean',
            'source_type' => 'nullable|string',
            'source_id'   => 'nullable|integer',
        ]);

        $bill = app(CreateApBillAction::class)->run($data);
        return Response::create($bill, ApBillTransformer::class)->created();
    }

    public function showBill(int $id)
    {
        $bill = ApBill::with(['supplier', 'period', 'settlements'])->findOrFail($id);
        return Response::create($bill, ApBillTransformer::class)->ok();
    }

    // ─── 付款单 (AP Payments) ───────────────────────────────────────────────────────

    public function indexPayments(Request $request)
    {
        $payments = app(ListApPaymentsAction::class)->run($request->all());
        return Response::create($payments, ApPaymentTransformer::class)->ok();
    }

    public function storePayment(Request $request)
    {
        $data = $request->validate([
            'period_id'    => 'required|integer',
            'supplier_id'  => 'required|integer',
            'payment_no'   => 'required|string|max:30',
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
        ]);

        $payment = app(CreateApPaymentAction::class)->run($data);
        return Response::create($payment, ApPaymentTransformer::class)->created();
    }

    public function showPayment(int $id)
    {
        $payment = ApPayment::with(['supplier', 'period', 'settlements'])->findOrFail($id);
        return Response::create($payment, ApPaymentTransformer::class)->ok();
    }

    // ─── 核销业务 (Settlement) ─────────────────────────────────────────────────────────

    public function settle(Request $request)
    {
        $data = $request->validate([
            'ap_bill_id'    => 'required|integer',
            'ap_payment_id' => 'required|integer',
            'amount'        => 'required|numeric|min:0.01',
        ]);

        try {
            app(SettleApAction::class)->run($data);
            return response()->json(['message' => 'Settled successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
