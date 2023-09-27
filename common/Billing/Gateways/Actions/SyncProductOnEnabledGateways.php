<?php

namespace Common\Billing\Gateways\Actions;

use Common\Billing\Gateways\Paypal\Paypal;
use Common\Billing\Gateways\Stripe\Stripe;
use Common\Billing\Models\Product;

class SyncProductOnEnabledGateways
{
    public function __construct(
        protected Stripe $stripe,
        protected Paypal $paypal
    ) {
    }

    public function execute(Product $product): void
    {
        @ini_set('max_execution_time', 300);

        if ($this->stripe->isEnabled()) {
            $this->stripe->syncPlan($product);
        }
        if ($this->paypal->isEnabled()) {
            $this->paypal->syncPlan($product);
        }
    }
}
