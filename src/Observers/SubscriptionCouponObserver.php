<?php

namespace SteadfastCollective\CashierExtended\Observers;

use Illuminate\Support\Carbon;
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
        $subscriptionCoupon->createStripeCoupon(array_filter([
            'id' => $subscriptionCoupon->code,
            'duration' => $subscriptionCoupon->duration,
            'amount_off' => $subscriptionCoupon->amount_off,
            'percent_off' => $subscriptionCoupon->percent_off,
            'duration_in_months' => $subscriptionCoupon->duration_in_months,
            'max_redemptions' => $subscriptionCoupon->max_redemptions,
            'times_redeemed' => $subscriptionCoupon->times_redeemed,
            'name' => $subscriptionCoupon->name,
            'redeem_by' => isset($subscriptionCoupon->redeem_by) ? Carbon::parse($subscriptionCoupon->redeem_by)->getTimestamp() : null,
            'currency' => config('cashier.currency'),
        ]));
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