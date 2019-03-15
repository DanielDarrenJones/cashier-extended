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
    public function handleChargeFailed($payload)
    {
        $this->updateCharge($payload);
    }

    /**
     * Handle Charge Updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleChargeUpdated($payload)
    {
        $this->updateCharge($payload);
    }

    /**
     * Handle Charge Succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleChargeSucceeded($payload)
    {
        $this->updateCharge($payload);
    }

    /**
     * Handle Charge Expired.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleChargeExpired($payload)
    {
        $this->updateCharge($payload);
    }

    /**
     * Handle Charge Refunded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleChargeRefunded($payload)
    {
        $this->updateCharge($payload);
    }

    /**
     * Handle Charge Refund Updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleChargeRefundUpdated($payload)
    {
        $this->updateCharge($payload);
    }

    private function updateCharge($payload)
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
                        $charge->paid = (bool) $data['paid'] ? now() : null;
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
