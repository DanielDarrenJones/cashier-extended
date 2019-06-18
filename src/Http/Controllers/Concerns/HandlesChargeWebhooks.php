<?php

namespace SteadfastCollective\CashierExtended\Http\Controllers\Concerns;

trait HandlesChargeWebhooks
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
