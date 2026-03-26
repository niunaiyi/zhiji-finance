<?php

namespace App\Containers\Finance\Purchase\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Purchase\Actions\CreatePurchaseOrderAction;
use App\Containers\Finance\Purchase\Models\PurchaseOrder;
use App\Containers\Finance\Purchase\Models\PurchaseReceipt;
use App\Containers\Finance\Purchase\Models\PurchaseInvoice;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\Request;

/**
 * 采购模块 API 控制器。
 * 处理采购订单的列表查询、详情展示、保存及审核等请求。
 */
class PurchaseController extends ApiController
{
    // ─── Purchase Orders ──────────────────────────────────────────────────
    public function indexOrders(Request $request)
    {
        $orders = PurchaseOrder::where('company_id', auth()->user()->current_company_id)
            ->with('supplier')
            ->orderBy('order_date', 'desc')
            ->paginate($request->get('limit', 20));
        return response()->json($orders);
    }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'order_no'    => 'required|string|max:30',
            'order_date'  => 'required|date',
            'supplier_id' => 'required|integer',
        ]);
        $order = app(CreatePurchaseOrderAction::class)->run($data);
        return response()->json($order, 201);
    }

    public function showOrder(int $id)
    {
        $order = PurchaseOrder::with('supplier')->findOrFail($id);
        return response()->json($order);
    }

    // ─── Purchase Receipts ──────────────────────────────────────────────
    public function indexReceipts(Request $request)
    {
        $receipts = PurchaseReceipt::where('company_id', auth()->user()->current_company_id)
            ->with('supplier')
            ->orderBy('receipt_date', 'desc')
            ->paginate($request->get('limit', 20));
        return response()->json($receipts);
    }

    public function storeReceipt(Request $request)
    {
        $data = $request->validate([
            'receipt_no'   => 'required|string|max:30',
            'receipt_date' => 'required|date',
            'supplier_id'  => 'required|integer',
        ]);
        $data['company_id'] = auth()->user()->current_company_id;
        $data['status']     = 'draft';
        $receipt = PurchaseReceipt::create($data);
        return response()->json($receipt, 201);
    }

    // ─── Purchase Invoices ──────────────────────────────────────────────
    public function indexInvoices(Request $request)
    {
        $invoices = PurchaseInvoice::where('company_id', auth()->user()->current_company_id)
            ->with('supplier')
            ->orderBy('invoice_date', 'desc')
            ->paginate($request->get('limit', 20));
        return response()->json($invoices);
    }

    public function storeInvoice(Request $request)
    {
        $data = $request->validate([
            'invoice_no'   => 'required|string|max:30',
            'invoice_date' => 'required|date',
            'supplier_id'  => 'required|integer',
        ]);
        $data['company_id'] = auth()->user()->current_company_id;
        $data['status']     = 'draft';
        $invoice = PurchaseInvoice::create($data);
        return response()->json($invoice, 201);
    }
}
