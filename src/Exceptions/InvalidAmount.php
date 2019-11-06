<?php

namespace SteadfastCollective\CashierExtended\Exceptions;

use Exception;

class InvalidAmount extends Exception
{
    /**
     * Create a new SubscriptionUpdateFailure instance.
     *
     * @param  string  $plan
     * @return self
     */
    public static function amountMustBeGreaterThanZero()
    {
        return new static("The final amount must be greater than zero.");
    }
}
