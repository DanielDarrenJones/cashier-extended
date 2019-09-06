<?php

namespace SteadfastCollective\CashierExtended;

use Laravel\Cashier\Billable as CashierBillable;
use Laravel\Cashier\Exceptions\IncompletePayment;

trait Billable
{
    use CashierBillable {
        charge as parentCharge;
    }

    public function charge($name, $amount, $paymentMethod, array $options = []) {
        try {
            $charge = $this->parentCharge($amount, $paymentMethod, $options);
        } catch (IncompletePayment $exception) {
            $this->charges()->create([
                'name' => $name,
                'stripe_id' => $exception->payment->id,
                'amount' => $exception->payment->amount,
                'amount_refunded' => 0,
                'currency' => $exception->payment->currency,
                'stripe_status' => $exception->payment->status,
                'paid_at' => $exception->payment->amount == 0 || $exception->payment->amount_received > 0 ? now() : null,
            ]);

            throw $exception;
        }

        // Save the charge
        return $this->charges()->create([
            'name' => $name,
            'stripe_id' => $charge->id,
            'amount' => $charge->amount,
            'amount_refunded' => $charge->amount_refunded,
            'currency' => $charge->currency,
            'stripe_status' => $exception->payment->status,
            'paid_at' => $charge->paid ? now() : null,
        ]);
    }

    /**
     * Check if a charge exists by name.
     *
     * @param  string  $charge
     * @return bool
     */
    public function purchased($charge)
    {
        return $this->charges()->where('name', $charge)->where('stripe_status', 'succeeded')->whereNotNull('paid_at')->exists();
    }

    /**
     * Find a charge by ID.
     *
     * @param  string  $id
     * @return \SteadfastCollective\CashierExtended\Charge|null
     */
    public function findCharge($id)
    {
        return $this->charges()->where('stripe_id', $id)->first();
    }

    /**
     * Find a charge by ID or throw a 404 error.
     *
     * @param  string  $id
     * @return \SteadfastCollective\CashierExtended\Charge
     */
    public function findChargeOrFail($id)
    {
        return $this->charges()->where('stripe_id', $id)->firstOrFail();
    }

    /**
     * Get all of the charges for the Stripe model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function charges()
    {
        return $this->hasMany(Charge::class, $this->getForeignKey())->orderBy('created_at', 'desc');
    }
}
