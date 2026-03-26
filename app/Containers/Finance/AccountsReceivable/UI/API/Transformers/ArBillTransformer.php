<?php

namespace App\Containers\Finance\AccountsReceivable\UI\API\Transformers;

use App\Containers\Finance\AccountsReceivable\Models\ArBill;
use App\Ship\Parents\Transformers\Transformer;

class ArBillTransformer extends Transformer
{
    protected array $defaultIncludes = [
    ];

    protected array $availableIncludes = [
    ];

    public function transform(ArBill $bill): array
    {
        return [
            'id'             => $bill->getHashedKey(),
            'bill_no'        => $bill->bill_no,
            'bill_date'      => $bill->bill_date->toDateString(),
            'customer_id'    => $bill->customer_id,
            'customer'       => $bill->customer ? [
                'id'   => $bill->customer->id,
                'name' => $bill->customer->name,
                'code' => $bill->customer->code,
            ] : null,
            'amount'         => $bill->amount,
            'settled_amount' => $bill->settled_amount,
            'balance'        => $bill->balance,
            'status'         => $bill->status,
            'source_type'    => $bill->source_type,
            'source_id'      => $bill->source_id,
            'created_at'     => $bill->created_at,
            'updated_at'     => $bill->updated_at,
        ];
    }
}
