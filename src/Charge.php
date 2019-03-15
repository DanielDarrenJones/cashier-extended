<?php

namespace SteadfastCollective\CashierExtended;

use Carbon\Carbon;
use LogicException;
use DateTimeInterface;
use Laravel\Cashier\Cashier;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'paid_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the user that owns the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->owner();
    }

    /**
     * Get the model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $class = Cashier::stripeModel();

        return $this->belongsTo($class, (new $class)->getForeignKey());
    }

    /**
     * Determine if the charge has been paid.
     *
     * @return boolean
     */
    public function paid()
    {
        return ! is_null($this->paid_at);
    }

    /**
     * Determine if the charge has not been paid.
     *
     * @return boolean
     */
    public function unpaid()
    {
        return is_null($this->paid_at);
    }

    /**
     * Filter query by paid.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopePaid($query)
    {
        $query->whereNotNull('paid_at');
    }

    /**
     * Filter query by paid.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeUnpaid($query)
    {
        $query->whereNull('paid_at');
    }

    /**
     * Filter query by currency.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeCurrency($query, $currency)
    {
        $query->where('currency', $currency);
    }

    /**
     * Filter query by amounts greater than.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeAmountGreaterThan($query, $amount)
    {
        $query->where('amount', '>', $amount);
    }

    /**
     * Filter query by amounts greater than or equal to.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeAmountGreaterThanEqualTo($query, $amount)
    {
        $query->where('amount', '>=', $amount);
    }

    /**
     * Filter query by amounts less than.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeAmountLessThan($query, $amount)
    {
        $query->where('amount', '<', $amount);
    }

    /**
     * Filter query by amounts less than or equal to.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeAmountLessThanEqualTo($query, $amount)
    {
        $query->where('amount', '<=', $amount);
    }

    /**
     * Filter query by amounts equal to.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeAmountEqualTo($query, $amount)
    {
        $query->where('amount', $amount);
    }

    /**
     * Get the subscription as a Stripe subscription object.
     *
     * @return \Stripe\Charge
     * @throws \LogicException
     */
    public function asStripeCharge()
    {
        $charges = $this->user->asStripeCustomer()->charges();

        if (! $charges) {
            throw new LogicException('The Stripe customer does not have any charges.');
        }

        return $charges->retrieve($this->stripe_id);
    }
}
