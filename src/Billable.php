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
        return $this->owner->charges()->create([
            'name' => $name,
            'stripe_id' => $charge->id,
            'amount' => $charge->amount,
            'quantity' => $charge->currency,
            'paid_at' => $charge->paid ? now() : null,
        ]);
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
