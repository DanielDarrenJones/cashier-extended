<?php

namespace SteadfastCollective\CashierExtended\Http\Controllers;

use Illuminate\Support\Carbon;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use SteadfastCollective\CashierExtended\Charge;
use SteadfastCollective\CashierExtended\SubscriptionCoupon;

class WebhookController extends CashierController
{
    /**
     * Handle Charge Failed.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleChargeFailed($payload)
    {
        return $this->updateCharge($payload);
    }

    /**
     * Handle Charge Updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleChargeUpdated($payload)
    {
        return $this->updateCharge($payload);
    }

    /**
     * Handle Charge Succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleChargeSucceeded($payload)
    {
        return $this->updateCharge($payload);
    }

    /**
     * Handle Charge Expired.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleChargeExpired($payload)
    {
        return $this->updateCharge($payload);
    }

    /**
     * Handle Charge Refunded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleChargeRefunded($payload)
    {
        return $this->updateCharge($payload);
    }

    /**
     * Handle Charge Refund Updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleChargeRefundUpdated($payload)
    {
        return $this->updateCharge($payload);
    }

    /**
     * Handle Payment Intent Succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handlePaymentIntentSucceeded($payload)
    {
        return $this->updatePaymentIntentCharge($payload);
    }

    /**
     * Handle Payment Intent Failed.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handlePaymentIntentPaymentFailed($payload)
    {
        return $this->updatePaymentIntentCharge($payload);
    }

    /**
     * Handle Coupon created.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCouponCreated($payload)
    {
        $coupon = $this->getCouponByStripeId($payload['data']['id']);

        if (!$coupon) {
            SubscriptionCoupon::create([
                'name' => $payload['data']['name'],
                'code' => $payload['data']['id'],
                'amount_off' => $payload['data']['amount_off'],
                'percent_off' => $payload['data']['percent_off'],
                'duration' => $payload['data']['duration'],
                'duration_in_months' => $payload['data']['duration_in_months'],
                'max_redemptions' => $payload['data']['max_redemptions'],
                'times_redeemed' => $payload['data']['times_redeemed'],
                'redeem_by' => Carbon::createFromTimestamp($payload['data']['redeem_by']),
                'valid' => $payload['data']['valid'],
            ]);
        }
        
        return $this->successMethod();
    }

    /**
     * Handle Coupon updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCouponUpdated($payload)
    {
        $coupon = $this->getCouponByStripeId($payload['data']['id']);

        if ($coupon) {
            $coupon->name = $payload['data']['name'];

            $coupon->valid = $payload['data']['valid'];

            $coupon->save();
        }

        return $this->successMethod();
    }

    /**
     * Handle Coupon deleted.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCouponDeleted($payload)
    {
        $coupon = $this->getCouponByStripeId($payload['data']['id']);

        if ($coupon) {
            $coupon->delete();
        }

        return $this->successMethod();
    }

    protected function updatePaymentIntentCharge($payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        if ($user) {
            $data = collect($payload['data']['object']['charges']['data'])->first();
        
            $user->charges()
                ->where('stripe_id', $payload['data']['object']['id'])
                ->get()
                ->each(function (Charge $charge) use ($data) {

                    // Update Payment Intent ID to Charge ID
                    // $charge->id = $data['id'];

                    // Paid...
                    if (isset($data['paid'])) {
                        $charge->paid_at = (bool) $data['paid'] ? now() : null;
                    }

                    // Amount...
                    if (isset($data['amount'])) {
                        $charge->amount = (int) $data['amount'];
                    }

                    // Amount Refunded...
                    if (isset($data['amount_refunded'])) {
                        $charge->amount_refunded = (int) $data['amount_refunded'];
                    }

                    // Status...
                    if (isset($data['status'])) {
                        $charge->stripe_status = $data['status'];
                    }

                    $charge->save();
                });
        }

        return $this->successMethod();
    }

    protected function updateCharge($payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        if ($user) {
            $data = $payload['data']['object'];

            $user->charges()
                ->where('stripe_id', $data['id'])
                ->get()
                ->each(function (Charge $charge) use ($data) {

                    // Paid...
                    if (isset($data['paid'])) {
                        $charge->paid_at = (bool) $data['paid'] ? now() : null;
                    }

                    // Amount...
                    if (isset($data['amount'])) {
                        $charge->amount = (int) $data['amount'];
                    }

                    // Amount Refunded...
                    if (isset($data['amount_refunded'])) {
                        $charge->amount_refunded = (int) $data['amount_refunded'];
                    }

                    // Status...
                    if (isset($data['status'])) {
                        $charge->stripe_status = $data['status'];
                    }

                    $charge->save();
                });
        }

        return $this->successMethod();
    }

    /**
     * Get the billable entity instance by Stripe ID.
     *
     * @param  string|null  $stripeId
     * @return \Laravel\Cashier\Billable|null
     */
    protected function getCouponByStripeId($stripeId)
    {
        if ($stripeId === null) {
            return;
        }

        return SubscriptionCoupon::where('code', $stripeId)->first();
    }
}
