<?php

namespace App\Containers\Finance\GeneralLedger\UI\API\Controllers;

use App\Containers\Finance\AccountsPayable\Models\ApBill;
use App\Containers\Finance\AccountsReceivable\Models\ArBill;
use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\GeneralLedger\Models\Balance;
use App\Ship\Parents\Controllers\ApiController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends ApiController
{
    /**
     * 科目余额表 (Trial Balance)
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Simple implementation: Fetch balances of the current period
        $period = Period::where('company_id', $companyId)
            ->whereDate('start_date', '<=', $startDate)
            ->whereDate('end_date', '>=', $endDate)
            ->first();

        if (!$period) {
             // Fallback to most recent period or empty
             return response()->json(['data' => []]);
        }

        $balances = Balance::with('account')
            ->where('company_id', $companyId)
            ->where('period_id', $period->id)
            ->get();

        $results = $balances->map(function ($b) {
            $account = $b->account;
            $opening = $account->balance_direction === 'debit' 
                ? (float)$b->opening_debit - (float)$b->opening_credit 
                : (float)$b->opening_credit - (float)$b->opening_debit;
            
            $closing = $account->balance_direction === 'debit'
                ? (float)$b->closing_debit - (float)$b->closing_credit
                : (float)$b->closing_credit - (float)$b->closing_debit;

            return [
                'subject_code' => $account->code,
                'subject_name' => $account->name,
                'balance_direction' => $account->balance_direction === 'debit' ? '借' : '贷',
                'opening_balance' => $opening,
                'period_debit' => (float)$b->period_debit,
                'period_credit' => (float)$b->period_credit,
                'closing_balance' => $closing,
            ];
        });

        return response()->json(['data' => $results]);
    }

    /**
     * 资产负债表 (Balance Sheet)
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id');
        $date = $request->get('date', now()->toDateString());

        $period = Period::where('company_id', $companyId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if (!$period) return response()->json(['data' => null]);

        $balances = Balance::with('account')
            ->where('company_id', $companyId)
            ->where('period_id', $period->id)
            ->get();

        $data = [
            'assets' => [],
            'liabilities' => [],
            'equity' => [],
            'total_assets' => 0,
            'total_liabilities' => 0,
            'total_equity' => 0,
            'total_liabilities_equity' => 0,
        ];

        foreach ($balances as $b) {
            $account = $b->account;
            $value = $account->balance_direction === 'debit'
                ? (float)$b->closing_debit - (float)$b->closing_credit
                : (float)$b->closing_credit - (float)$b->closing_debit;

            if ($value == 0) continue;

            $item = [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $value
            ];

            // Manual mapping based on code prefix or type
            // 1xx Assets, 2xx Liabilities, 3xx/4xx Equity, 5xx Costs, 6xx Revenue/Expense
            if (str_starts_with($account->code, '1')) {
                $data['assets'][] = $item;
                $data['total_assets'] += $value;
            } elseif (str_starts_with($account->code, '2')) {
                $data['liabilities'][] = $item;
                $data['total_liabilities'] += $value;
            } elseif (str_starts_with($account->code, '3') || str_starts_with($account->code, '4')) {
                $data['equity'][] = $item;
                $data['total_equity'] += $value;
            }
        }

        $data['total_liabilities_equity'] = $data['total_liabilities'] + $data['total_equity'];

        return response()->json(['data' => $data]);
    }

    /**
     * 利润表 (Income Statement)
     */
    public function incomeStatement(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $period = Period::where('company_id', $companyId)
            ->whereDate('start_date', '<=', $startDate)
            ->whereDate('end_date', '>=', $endDate)
            ->first();

        if (!$period) return response()->json(['data' => null]);

        $balances = Balance::with('account')
            ->where('company_id', $companyId)
            ->where('period_id', $period->id)
            ->get();

        $rows = [];
        $totalRev = 0;
        $totalCost = 0;

        foreach ($balances as $b) {
            $account = $b->account;
            // Revenue (6xx usually) or Expense
            if (!str_starts_with($account->code, '5') && !str_starts_with($account->code, '6')) continue;

            $amt = (float)$b->period_debit + (float)$b->period_credit; // Simple version
            if ($amt == 0) continue;

            $isRev = str_starts_with($account->code, '60'); // Assuming 60xx is revenue
            
            $rows[] = [
                'code' => $account->code,
                'name' => $account->name,
                'amount' => $amt,
                'type' => $isRev ? 'REVENUE' : 'EXPENSE'
            ];

            if ($isRev) $totalRev += $amt;
            else $totalCost += $amt;
        }

        return response()->json(['data' => [
            'rows' => $rows,
            'total_revenue' => $totalRev,
            'total_cost' => $totalCost,
            'net_profit' => $totalRev - $totalCost
        ]]);
    }

    /**
     * 账龄分析 (AR/AP Aging Analysis)
     */
    public function agingAnalysis(Request $request)
    {
        $type = $request->get('type', 'AR'); // AR or AP
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : now();

        $results = [];

        if ($type === 'AR') {
            $bills = ArBill::where('bill_date', '<=', $date->toDateString())
                ->where('balance', '>', 0)
                ->with('customer')
                ->get();
            $grouped = $bills->groupBy('customer_id');
        } else {
            $bills = ApBill::where('bill_date', '<=', $date->toDateString())
                ->where('balance', '>', 0)
                ->with('supplier')
                ->get();
            $grouped = $bills->groupBy('supplier_id');
        }

        foreach ($grouped as $auxItemId => $groupBills) {
            $total = 0;
            $b1 = 0; $b2 = 0; $b3 = 0; $b4 = 0;
            $name = ''; $code = '';

            foreach ($groupBills as $bill) {
                if ($type === 'AR') {
                    $name = $bill->customer ? $bill->customer->name : "Customer #{$auxItemId}";
                    $code = $bill->customer ? $bill->customer->code : "C-{$auxItemId}";
                } else {
                    $name = $bill->supplier ? $bill->supplier->name : "Supplier #{$auxItemId}";
                    $code = $bill->supplier ? $bill->supplier->code : "S-{$auxItemId}";
                }

                $balance = (float) $bill->balance;
                $total += $balance;
                $days = Carbon::parse($bill->bill_date)->diffInDays($date, false);

                if ($days <= 30) $b1 += $balance;
                elseif ($days <= 60) $b2 += $balance;
                elseif ($days <= 90) $b3 += $balance;
                else $b4 += $balance;
            }

            $results[] = [
                'name' => $name,
                'code' => $code,
                'total' => $total,
                'bucket_1_30' => $b1,
                'bucket_31_60' => $b2,
                'bucket_61_90' => $b3,
                'bucket_91_plus' => $b4,
            ];
        }

        return response()->json(['data' => $results]);
    }
}
