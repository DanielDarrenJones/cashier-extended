<?php

namespace SteadfastCollective\CashierExtended;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use SteadfastCollective\CashierExtended\Charge;

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
     * Handle customer subscription updated.
     *
     * @param  array $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        return $this->updateSubscription($payload);
    }

    protected function updateSubscription($payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            $data = $payload['data']['object'];
            $user->subscriptions()
                ->where('stripe_id', $data['id'])
                ->get()
                ->each(function (Subscription $subscription) use ($data) {
                    // Quantity...
                    if (isset($data['quantity'])) {
                        $subscription->quantity = $data['quantity'];
                    }

                    // Plan...
                    if (isset($data['plan']['id'])) {
                        $subscription->stripe_plan = $data['plan']['id'];
                    }

                    // Trial ending date...
                    if (isset($data['trial_end'])) {
                        $trial_ends = Carbon::createFromTimestamp($data['trial_end']);
                        if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trial_ends)) {
                            $subscription->trial_ends_at = $trial_ends;
                        }
                    }

                    // Cancellation date...
                    if (isset($data['cancel_at_period_end']) && $data['cancel_at_period_end']) {
                        $subscription->ends_at = $subscription->onTrial()
                            ? $subscription->trial_ends_at
                            : Carbon::createFromTimestamp($data['current_period_end']);
                    }

                    // Status...
                    if (isset($data['status']) && $data['status']) {
                        $subscription->status = $data['status'];
                    }

                    $subscription->save();
                });
        }
        return new Response('Webhook Handled', 200);
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

                    $charge->save();
                });
        }

        return new Response('Webhook Handled', 200);
    }
}
