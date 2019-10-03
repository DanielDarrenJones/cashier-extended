<?php

namespace SteadfastCollective\CashierExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'percent_off' => 'decimal:5',
        'max_redemptions' => 'integer',
        'times_redeemed' => 'integer',
        'valid' => 'boolean',
    ];

    /**
     * Get the coupon as a Stripe coupon object.
     *
     * @return \Stripe\Coupon
     */
    public function asStripeCoupon() : StripeCoupon
    {
        return StripeCoupon::retrieve(
            $this->code, 
            CashierExtended::stripeOptions()
        );
    }

    /**
     * Sync the coupon with the Stripe coupon object.
     *
     * @return void
     */
    public function syncCoupon() : void
    {
        $stripeCoupon = $this->asStripeCoupon();

        $this->valid = $stripeCoupon->valid;

        $this->times_redeemed = $stripeCoupon->times_redeemed;

        $this->save();
    }

    /**
     * Create Stripe coupon.
     *
     * @param array $params
     * @return void
     */
    public function createStripeCoupon(array $params) : void
    {
        StripeCoupon::create($params, CashierExtended::stripeOptions());
    }

    /**
     * Update Stripe coupon.
     *
     * @param string $stripeId
     * @param array $params
     * @return void
     */
    public function updateStripeCoupon(string $stripeId, array $params) : void
    {
        StripeCoupon::update($stripeId, [
            'name' => $params['name'],
            // 'metadata' => [],
        ], CashierExtended::stripeOptions());
    }

    /**
     * Delete Stripe coupon.
     *
     * @return void
     */
    public function deleteStripeCoupon(string $stripeId) : void
    {
        $coupon = StripeCoupon::retrieve(
            $stripeId, 
            CashierExtended::stripeOptions()
        );

        $coupon->delete();
    }

}
