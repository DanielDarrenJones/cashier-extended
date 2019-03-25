<?php

namespace SteadfastCollective\CashierExtended;

use Laravel\Cashier\Billable as CashierBillable;

trait Billable
{
    use CashierBillable {
        charge as parentCharge;
    }

    public function charge($name, $amount) {
        $charge = $this->parentCharge($amount);

        // Save the charge
        return $this->charges()->create([
            'name' => $name,
            'stripe_id' => $charge->id,
            'amount' => $charge->amount,
            'amount_refunded' => $charge->amount_refunded,
            'currency' => $charge->currency,
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
        return $this->charges()->where('name', $charge)->exists();
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
