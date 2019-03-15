<?php

namespace SteadfastCollective\CashierExtended;

use Laravel\Cashier\Cashier;

class CashierExtended extends Cashier
{
    public function __construct()
    {
        parent::__construct();
    }
}
