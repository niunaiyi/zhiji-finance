<?php

namespace App\Containers\Finance\GeneralLedger\Providers;

use App\Containers\Finance\GeneralLedger\Listeners\UpdateBalanceOnVoucherPosted;
use App\Containers\Finance\Voucher\Events\VoucherPostedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        VoucherPostedEvent::class => [
            UpdateBalanceOnVoucherPosted::class,
        ],
    ];
}
