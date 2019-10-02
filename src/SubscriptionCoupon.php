<?php

namespace SteadfastCollective\CashierExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Stripe\Coupon as StripeCoupon;

class SubscriptionCoupon extends Model
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
        'expires_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'amount_off' => 'integer',
        'percent_off' => 'decimal',
        'max_redemptions' => 'integer',
    ];

    /**
     * Get the coupon as a Stripe coupon object.
     *
     * @return \Stripe\Coupon
     */
    public function asStripeCoupon() : StripeCoupon
    {
        return StripeCoupon::retrieve(
            $this->stripe_id, 
            CashierExtended::stripeOptions()
        );
    }

    /**
     * Sync the coupon.
     *
     * @return void
     */
    public function syncCoupon() : void
    {
        $coupon = $this->asStripeCoupon();

        $this->max_redemptions = $coupon->max_redemptions;

        $this->redeem_by = Carbon::createFromTimestamp($coupon->redeem_by);

        $this->valid = $coupon->valid;
        
        $this->save();
    }

}
