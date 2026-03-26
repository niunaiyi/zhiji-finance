<?php

namespace App\Containers\Finance\AccountsReceivable\UI\API\Transformers;

use App\Containers\Finance\AccountsReceivable\Models\ArReceipt;
use App\Ship\Parents\Transformers\Transformer;

class ArReceiptTransformer extends Transformer
{
    protected array $defaultIncludes = [
    ];

    protected array $availableIncludes = [
    ];

    public function transform(ArReceipt $receipt): array
    {
        return [
            'id'             => $receipt->getHashedKey(),
            'receipt_no'     => $receipt->receipt_no,
            'receipt_date'   => $receipt->receipt_date->toDateString(),
            'customer_id'    => $receipt->customer_id,
            'customer'       => $receipt->customer ? [
                'id'   => $receipt->customer->id,
                'name' => $receipt->customer->name,
                'code' => $receipt->customer->code,
            ] : null,
            'amount'         => $receipt->amount,
            'settled_amount' => $receipt->settled_amount,
            'balance'        => $receipt->balance,
            'status'         => $receipt->status,
            'created_at'     => $receipt->created_at,
            'updated_at'     => $receipt->updated_at,
        ];
    }
}
