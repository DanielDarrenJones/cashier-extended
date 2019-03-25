<?php

namespace SteadfastCollective\CashierExtended\Contracts\Repositories;

interface CouponRepository
{
    /**
     * Retrieve all coupons.
     *
     * @return array
     */
    public function index();

    /**
     * Show the coupon data for the given code.
     *
     * @param  string  $code
     * @return \App\Coupon
     */
    public function show($code);
}
