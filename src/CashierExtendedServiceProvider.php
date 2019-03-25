<?php

namespace SteadfastCollective\CashierExtended;

use Illuminate\Support\ServiceProvider;

class CashierExtendedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if (! class_exists('CreateChargesTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_charges_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_charges_table.php'),
            ], 'migrations');
        }

        // if (! class_exists('CreateInvoicesTable')) {
        //     $this->publishes([
        //         __DIR__.'/../database/migrations/create_invoices_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_invoices_table.php'),
        //     ], 'migrations');
        // }

        // if (! class_exists('CreateSubscriptionsTable')) {
        //     $this->publishes([
        //         __DIR__.'/../database/migrations/create_subscriptions_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_subscriptions_table.php'),
        //     ], 'migrations');
        // }

        // if (! class_exists('UpdateUsersTable')) {
        //     $this->publishes([
        //         __DIR__.'/../database/migrations/update_users_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_update_users_table.php'),
        //     ], 'migrations');
        // }
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

        // Coupons
        $this->app->bind(
            \SteadfastCollective\CashierExtended\Contracts\Repositories\CouponRepository::class,
            \SteadfastCollective\CashierExtended\Repositories\CouponRepository::class
        );
    }
}
