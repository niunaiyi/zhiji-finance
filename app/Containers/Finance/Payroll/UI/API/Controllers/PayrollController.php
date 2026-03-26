<?php

namespace App\Containers\Finance\Payroll\UI\API\Controllers;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\Payroll\Models\Payroll;
use App\Containers\Finance\Payroll\Models\PayrollItem;
use App\Containers\Finance\Payroll\Models\PayrollLine;
use App\Containers\Finance\Payroll\Actions\CalculatePayrollAction;
use App\Containers\Finance\Payroll\Actions\ListPayrollItemsAction;
use App\Containers\Finance\Payroll\Actions\ListPayrollsAction;
use App\Containers\Finance\Payroll\Actions\SavePayrollItemAction;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends ApiController
{
    /**
     * List all payroll items (Earnings/Deductions).
     */
    public function listItems(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id');
        $items = app(ListPayrollItemsAction::class)->run((int)$companyId);
        return response()->json(['data' => $items]);
    }

    /**
     * Create/Update payroll item.
     */
    public function saveItem(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id');
        $data = $request->validate([
            'id' => 'nullable|integer|exists:payroll_items,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:50',
            'type' => 'required|string|in:earning,deduction',
        ]);

        $data['company_id'] = $companyId;
        $item = app(SavePayrollItemAction::class)->run($data);
        return response()->json(['data' => $item]);
    }

    /**
     * Calculate monthly payroll.
     */
    public function calculate(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        $data = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $result = app(CalculatePayrollAction::class)->run($companyId, $data['year'], $data['month']);

        return response()->json([
            'message' => '工资发放表已生成',
            'data' => $result['payroll'],
            'summary' => $result['summary'],
        ]);
    }

    /**
     * List all payroll records.
     */
    public function listPayrolls(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        $payrolls = app(ListPayrollsAction::class)->run($companyId);
        return response()->json(['data' => $payrolls]);
    }
}
