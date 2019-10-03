<?php

namespace SteadfastCollective\CashierExtended\Observers;

use SteadfastCollective\CashierExtended\SubscriptionCoupon;

class SubscriptionCouponObserver
{
    /**
     * Handle the Subscription Coupon "creating" event.
     *
     * @param  \SteadfastCollective\CashierExtended\SubscriptionCoupon  $subscriptionCoupon
     * @return void
     */
    public function creating(SubscriptionCoupon $subscriptionCoupon)
    {
        $subscriptionCoupon->createStripeCoupon([
            'id' => $subscriptionCoupon->code,
            'duration' => $subscriptionCoupon->duration,
            'amount_off' => $subscriptionCoupon->amount_off,
            'percent_off' => $subscriptionCoupon->percent_off,
            'duration_in_months' => $subscriptionCoupon->duration_in_months,
            'max_redemptions' => $subscriptionCoupon->max_redemptions,
            'name' => $subscriptionCoupon->name,
            'redeem_by' => isset($subscriptionCoupon->redeem_by) ? Carbon::parse($subscriptionCoupon->redeem_by)->getTimestamp() : null,
        ]);
    }

    /**
     * Handle the Subscription Coupon "updating" event.
     *
     * @param  \SteadfastCollective\CashierExtended\SubscriptionCoupon  $subscriptionCoupon
     * @return void
     */
    public function updating(SubscriptionCoupon $subscriptionCoupon)
    {
        $subscriptionCoupon->updateStripeCoupon($subscriptionCoupon->code, [
            'name' => $subscriptionCoupon->name,
        ]);
    }

    /**
     * Handle the Subscription Coupon "deleting" event.
     *
     * @param  \SteadfastCollective\CashierExtended\SubscriptionCoupon  $subscriptionCoupon
     * @return void
     */
    public function deleting(SubscriptionCoupon $subscriptionCoupon)
    {
        $subscriptionCoupon->deleteStripeCoupon($subscriptionCoupon->code);
    }
}