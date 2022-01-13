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
        $coupon = $this->getCouponByStripeId($payload['data']['object']['id']);

        if (! $coupon) {
            SubscriptionCoupon::withoutEvents(function () use ($payload) {
                SubscriptionCoupon::create([
                    'name' => $payload['data']['object']['name'],
                    'code' => $payload['data']['object']['id'],
                    'amount_off' => $payload['data']['object']['amount_off'],
                    'percent_off' => $payload['data']['object']['percent_off'],
                    'duration' => $payload['data']['object']['duration'],
                    'duration_in_months' => $payload['data']['object']['duration_in_months'],
                    'max_redemptions' => $payload['data']['object']['max_redemptions'],
                    'times_redeemed' => $payload['data']['object']['times_redeemed'],
                    'redeem_by' => isset($payload['data']['object']['redeem_by']) ? Carbon::createFromTimestamp($payload['data']['object']['redeem_by']) : null,
                    'valid' => $payload['data']['object']['valid'],
                ]);
            });
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
        $coupon = $this->getCouponByStripeId($payload['data']['object']['id']);

        if ($coupon) {
            $coupon->name = $payload['data']['object']['name'];

            $coupon->valid = $payload['data']['object']['valid'];

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
        $coupon = $this->getCouponByStripeId($payload['data']['object']['id']);

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

                    // Charge ID...
                    if (isset($data['id'])) {
                        $charge->stripe_charge_id = $data['id'];
                    }

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
                ->where('stripe_charge_id', $data['id'])
                ->get()
                ->each(function (Charge $charge) use ($data) {

                    // Status...
                    if (isset($data['status'])) {
                        $charge->stripe_status = $data['status'];

                        // Paid at
                        if ($data['status'] == 'succeeded') {
                            $charge->paid_at = (bool) $data['paid'] ? now() : null;
                        }
                    }

                    // Amount...
                    if (isset($data['amount'])) {
                        $charge->amount = (int) $data['amount'];
                    }

                    // Amount Refunded...
                    if (isset($data['amount_refunded'])) {
                        $charge->amount_refunded = (int) $data['amount_refunded'];
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
