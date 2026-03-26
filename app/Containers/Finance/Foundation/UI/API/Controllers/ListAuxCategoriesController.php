<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\ListAuxCategoriesAction;
use App\Containers\Finance\Foundation\UI\API\Requests\ListAuxCategoriesRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxCategoryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

/**
 * 获取辅助核算类别列表控制器。
 * 用于在前端下拉查看可用的辅助核算类型（如：项目、部门、供应商等）。
 */
class ListAuxCategoriesController extends ApiController
{
    public function __construct(
        private readonly ListAuxCategoriesAction $action
    ) {}

    public function __invoke(ListAuxCategoriesRequest $request): JsonResponse
    {
        $auxCategories = $this->action->run($request->validated());
        return Response::create($auxCategories, AuxCategoryTransformer::class)->ok();
    }
}
