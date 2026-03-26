<?php

namespace App\Containers\Finance\Voucher\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Voucher\Actions\CreateVoucherAction;
use App\Containers\Finance\Voucher\Actions\GetVoucherAction;
use App\Containers\Finance\Voucher\Actions\ListVouchersAction;
use App\Containers\Finance\Voucher\Actions\PostVoucherAction;
use App\Containers\Finance\Voucher\Actions\ReverseVoucherAction;
use App\Containers\Finance\Voucher\Actions\ReviewVoucherAction;
use App\Containers\Finance\Voucher\Actions\VoidVoucherAction;
use App\Containers\Finance\Voucher\UI\API\Transformers\VoucherTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['period_id', 'status', 'voucher_type', 'keyword', 'per_page']);
        $vouchers = app(ListVouchersAction::class)->run($filters);

        return Response::create($vouchers, VoucherTransformer::class)->ok();
    }

    public function store(Request $request): JsonResponse
    {
        $voucher = app(CreateVoucherAction::class)->run($request->all());

        return Response::create($voucher, VoucherTransformer::class)->created();
    }

    public function show(int $id): JsonResponse
    {
        $voucher = app(GetVoucherAction::class)->run($id);

        return Response::create($voucher, VoucherTransformer::class)->ok();
    }

    public function review(int $id): JsonResponse
    {
        $voucher = app(ReviewVoucherAction::class)->run($id);

        return Response::create($voucher, VoucherTransformer::class)->ok();
    }

    public function post(int $id): JsonResponse
    {
        $voucher = app(PostVoucherAction::class)->run($id);

        return Response::create($voucher, VoucherTransformer::class)->ok();
    }

    public function reverse(int $id): JsonResponse
    {
        $voucher = app(ReverseVoucherAction::class)->run($id);

        return Response::create($voucher, VoucherTransformer::class)->ok();
    }

    public function void(int $id): JsonResponse
    {
        $voucher = app(VoidVoucherAction::class)->run($id);

        return Response::create($voucher, VoucherTransformer::class)->ok();
    }
}
