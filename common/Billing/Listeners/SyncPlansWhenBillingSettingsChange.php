<?php

namespace Common\Billing\Listeners;

use Common\Billing\Gateways\Paypal\Paypal;
use Common\Billing\Gateways\Stripe\Stripe;
use Common\Billing\Models\Product;
use Common\Settings\Events\SettingsSaved;

class SyncPlansWhenBillingSettingsChange
{
    public function __construct(
        protected Stripe $stripe,
        protected Paypal $paypal
    ) {
    }

    public function handle(SettingsSaved $event): void
    {
        $s = $event->envSettings;
        @ini_set('max_execution_time', 300);
        $products = Product::where('free', false)->get();

        if (
            array_key_exists('stripe_key', $s) ||
            array_key_exists('stripe_secret', $s)
        ) {
            $products->each(
                fn(Product $product) => $this->stripe->syncPlan($product),
            );
        }

        if (
            array_key_exists('paypal_client_id', $s) ||
            array_key_exists('paypal_secret', $s)
        ) {
            $products->each(
                fn(Product $product) => $this->paypal->syncPlan($product),
            );
        }
    }
}
