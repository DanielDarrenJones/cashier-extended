<?php

namespace SteadfastCollective\CashierExtended\Repositories;

use Exception;
use Stripe\Coupon as StripeCoupon;
use SteadfastCollective\CashierExtended\Coupon;
use SteadfastCollective\CashierExtended\Contracts\Repositories\CouponRepository as CouponRepositoryContract;

class CouponRepository implements CouponRepositoryContract
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $coupons = StripeCoupon::all(null, ['api_key' => $this->getStripeKey()]);

            $coupons = collect($coupons['data'])
                ->filter(function($coupon) {
                    return $coupon->valid;
                })
                ->map(function($coupon) {
                    return $this->toCoupon($coupon);
                });

            return $coupons;


        } catch (Exception $e) {
            return collect([]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function show($code)
    {
        try {
            $coupon = StripeCoupon::retrieve($code, ['api_key' => $this->getStripeKey()]);

            if ($coupon && $coupon->valid) {
                return $this->toCoupon($coupon);
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Convert the given Stripe coupon into a Coupon instance.
     *
     * @param  StripeCoupon  $coupon
     * @return SteadfastCollective\CashierExtended\Coupon
     */
    protected function toCoupon($coupon)
    {
        return new Coupon(
            $coupon->id,
            $coupon->duration,
            $coupon->duration_in_months,
            $coupon->amount_off,
            $coupon->percent_off
        );
    }

    /**
     * Get the Stripe API key.
     *
     * @return string
     */
    private function getStripeKey()
    {
        if ($key = getenv('STRIPE_SECRET')) {
            return $key;
        }

        return config('services.stripe.secret');
    }
}
