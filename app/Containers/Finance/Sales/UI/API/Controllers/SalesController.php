<?php

namespace App\Containers\Finance\Sales\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Sales\Actions\CreateSalesOrderAction;
use App\Containers\Finance\Sales\Models\SalesOrder;
use App\Containers\Finance\Sales\Models\SalesShipment;
use App\Containers\Finance\Sales\Models\SalesInvoice;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\Request;

class SalesController extends ApiController
{
    // ─── Sales Orders ──────────────────────────────────────────────────
    public function indexOrders(Request $request)
    {
        $orders = SalesOrder::where('company_id', auth()->user()->current_company_id)
            ->with('customer')
            ->orderBy('order_date', 'desc')
            ->paginate($request->get('limit', 20));
        return response()->json($orders);
    }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'order_no'    => 'required|string|max:30',
            'order_date'  => 'required|date',
            'customer_id' => 'required|integer',
        ]);
        $order = app(CreateSalesOrderAction::class)->run($data);
        return response()->json($order, 201);
    }

    public function showOrder(int $id)
    {
        $order = SalesOrder::with('customer')->findOrFail($id);
        return response()->json($order);
    }

    // ─── Sales Shipments ──────────────────────────────────────────────
    public function indexShipments(Request $request)
    {
        $shipments = SalesShipment::where('company_id', auth()->user()->current_company_id)
            ->with('customer')
            ->orderBy('shipment_date', 'desc')
            ->paginate($request->get('limit', 20));
        return response()->json($shipments);
    }

    public function storeShipment(Request $request)
    {
        $data = $request->validate([
            'shipment_no'   => 'required|string|max:30',
            'shipment_date' => 'required|date',
            'customer_id'   => 'required|integer',
        ]);
        $data['company_id'] = auth()->user()->current_company_id;
        $data['status']     = 'draft';
        $shipment = SalesShipment::create($data);
        return response()->json($shipment, 201);
    }

    // ─── Sales Invoices ──────────────────────────────────────────────
    public function indexInvoices(Request $request)
    {
        $invoices = SalesInvoice::where('company_id', auth()->user()->current_company_id)
            ->with('customer')
            ->orderBy('invoice_date', 'desc')
            ->paginate($request->get('limit', 20));
        return response()->json($invoices);
    }

    public function storeInvoice(Request $request)
    {
        $data = $request->validate([
            'invoice_no'   => 'required|string|max:30',
            'invoice_date' => 'required|date',
            'customer_id'   => 'required|integer',
        ]);
        $data['company_id'] = auth()->user()->current_company_id;
        $data['status']     = 'draft';
        $invoice = SalesInvoice::create($data);
        return response()->json($invoice, 201);
    }
}
