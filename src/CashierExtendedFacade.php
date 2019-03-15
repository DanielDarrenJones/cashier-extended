<?php

namespace SteadfastCollective\CashierExtended;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SteadfastCollective\CashierExtended\Skeleton\SkeletonClass
 */
class CashierExtendedFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cashier-extended';
    }
}
