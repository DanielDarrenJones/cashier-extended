<?php

namespace SteadfastCollective\CashierExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargeCoupon extends Model
{
    use SoftDeletes;

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
        'redeem_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'amount_off' => 'integer',
        'percent_off' => 'decimal:2',
        'max_redemptions' => 'integer',
        'times_redeemed' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'valid',
    ];

    /**
     * Calculate the Coupon discount.
     *
     * @param  int $amount
     * @return \Stripe\Coupon
     */
    public function calculateFinalAmount(int $amount) : int
    {
        if ($this->amount_off !== null) {
            $amount = $amount - $this->amount_off;
        } elseif ($this->percent_off !== null) {
            $amount = $amount - ($amount * ($this->percent_off / 100));
        }

        $amount = (int) (round($amount));

        return $amount;
    }

    /**
     * Increment the times redeemed of the Coupon.
     *
     * @param  int  $count
     * @return $this
     */
    public function incrementTimesRedeemed(int $count = 1) : self
    {
        $this->updateTimesRedeemed($this->times_redeemed + $count);

        return $this;
    }

    /**
     * Increment the times redeemed of the Coupon.
     *
     * @param  int  $count
     * @return $this
     */
    public function decrementTimesRedeemed(int $count = 1) : self
    {
        $this->updateTimesRedeemed(max(1, $this->times_redeemed - $count));

        return $this;
    }

    /**
     * Set times redeemed of the Coupon.
     *
     * @param  int  $count
     * @return $this
     */
    public function updateTimesRedeemed(int $timesRedeemed) : self
    {
        $this->times_redeemed = $timesRedeemed;

        $this->save();

        return $this;
    }
    
    /**
     * Determine if Coupon is valid.
     *
     * @return bool
     */
    public function getValidAttribute() : bool
    {
        return $this->redeemByIsValid() && $this->timesRedeemedIsValid();
    }

    /**
     * Determine if Coupon redeem by date is valid.
     *
     * @return bool
     */
    private function redeemByIsValid() : bool
    {
        return is_null($this->redeem_by) || ($this->redeem_by && $this->redeem_by->isFuture());
    }

    /**
     * Determine if Coupon if times redeemed count is valid.
     *
     * @return bool
     */
    private function timesRedeemedIsValid() : bool
    {
        return is_null($this->max_redemptions) || $this->times_redeemed < $this->max_redemptions;
    }

}
