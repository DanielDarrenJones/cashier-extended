<?php

namespace SteadfastCollective\CashierExtended\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use SteadfastCollective\CashierExtended\Charge;
use Illuminate\Support\Facades\Log;

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
     * Handle Payment Intent Succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handlePaymentIntentPaymentFailed($payload)
    {
        return $this->updatePaymentIntentCharge($payload);
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
}
