<?php

namespace App\Containers\Finance\AccountsPayable\UI\API\Transformers;

use App\Containers\Finance\AccountsPayable\Models\ApPayment;
use App\Ship\Parents\Transformers\Transformer;

class ApPaymentTransformer extends Transformer
{
    protected array $defaultIncludes = [
    ];

    protected array $availableIncludes = [
    ];

    public function transform(ApPayment $payment): array
    {
        return [
            'id'             => $payment->getHashedKey(),
            'payment_no'     => $payment->payment_no,
            'payment_date'   => $payment->payment_date->toDateString(),
            'supplier_id'    => $payment->supplier_id,
            'supplier'       => $payment->supplier ? [
                'id'   => $payment->supplier->id,
                'name' => $payment->supplier->name,
                'code' => $payment->supplier->code,
            ] : null,
            'amount'         => $payment->amount,
            'settled_amount' => $payment->settled_amount,
            'balance'        => $payment->balance,
            'status'         => $payment->status,
            'created_at'     => $payment->created_at,
            'updated_at'     => $payment->updated_at,
        ];
    }
}
