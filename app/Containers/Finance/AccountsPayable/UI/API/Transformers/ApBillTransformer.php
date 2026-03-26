<?php

namespace App\Containers\Finance\AccountsPayable\UI\API\Transformers;

use App\Containers\Finance\AccountsPayable\Models\ApBill;
use App\Ship\Parents\Transformers\Transformer;

class ApBillTransformer extends Transformer
{
    protected array $defaultIncludes = [
    ];

    protected array $availableIncludes = [
    ];

    public function transform(ApBill $bill): array
    {
        return [
            'id'             => $bill->getHashedKey(),
            'bill_no'        => $bill->bill_no,
            'bill_date'      => $bill->bill_date->toDateString(),
            'supplier_id'    => $bill->supplier_id,
            'supplier'       => $bill->supplier ? [
                'id'   => $bill->supplier->id,
                'name' => $bill->supplier->name,
                'code' => $bill->supplier->code,
            ] : null,
            'amount'         => $bill->amount,
            'settled_amount' => $bill->settled_amount,
            'balance'        => $bill->balance,
            'status'         => $bill->status,
            'is_estimate'    => $bill->is_estimate,
            'source_type'    => $bill->source_type,
            'source_id'      => $bill->source_id,
            'created_at'     => $bill->created_at,
            'updated_at'     => $bill->updated_at,
        ];
    }
}
