<?php

namespace SteadfastCollective\CashierExtended\Commands;

use Illuminate\Console\Command;
use SteadfastCollective\CashierExtended\SubscriptionCoupon;

class SyncCouponsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashierextended:sync-subscription-coupons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync valid subscription coupons to update valid and redemptions attributes.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $coupons = SubscriptionCoupon::cursor()->filter(function ($coupon) {
            return $coupon->valid;
        });

        $progressBar = $this->output->createProgressBar($coupons->count());

        $this->errorMessages = [];

        foreach ($coupons as $coupon) {
            try {
                $coupon->syncCoupon();
            } catch (\Exception $exception) {
                $this->errorMessages[$coupon->getKey()] = $exception->getMessage();
            }
            $progressBar->advance();
        }

        $progressBar->finish();

        if (count($this->errorMessages)) {
            $this->warn("\nAll done, but with some error messages:");
            foreach ($this->errorMessages as $couponId => $message) {
                $this->warn("\nCoupon id {$couponId}: `{$message}`");
            }
        }

        $this->info("\nAll done!");
    }
}
