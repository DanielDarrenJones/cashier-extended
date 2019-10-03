<?php

namespace SteadfastCollective\CashierExtended;

use Illuminate\Support\ServiceProvider;
use SteadfastCollective\CashierExtended\Observers\SubscriptionCouponObserver;

class CashierExtendedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        SubscriptionCoupon::observe(SubscriptionCouponObserver::class);

        if (! class_exists('CreateChargesTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_charges_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_charges_table.php'),
            ], 'migrations');
        }

        if (! class_exists('CreateChargeCouponsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_charge_coupons_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_charge_coupons_table.php'),
            ], 'migrations');
        }

        if (! class_exists('CreateSubscriptionCouponsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_subscription_coupons_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_subscription_coupons_table.php'),
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Register the main class to use with the facade
        $this->app->singleton('cashier-extended', function () {
            return new CashierExtended;
        });
        
    }
}
