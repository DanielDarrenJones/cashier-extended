<?php

namespace SteadfastCollective\CashierExtended\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use SteadfastCollective\CashierExtended\Charge;

class WebhookController extends CashierController
{
    use Concerns\HandlesChargeWebhooks,
        Concerns\HandlesCouponWebhooks,
        Concerns\HandlesSubscriptionWebhooks;
}
