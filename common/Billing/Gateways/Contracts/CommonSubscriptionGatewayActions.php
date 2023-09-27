<?php

namespace Common\Billing\Gateways\Contracts;

use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Subscription;

interface CommonSubscriptionGatewayActions
{
    public function isEnabled(): bool;

    /**
     * Sync plan from local database with the gateway
     */
    public function syncPlan(Product $product): bool;

    public function deletePlan(Product $product): bool;

    public function changePlan(
        Subscription $subscription,
        Product $newProduct,
        Price $newPrice
    ): bool;

    public function cancelSubscription(
        Subscription $subscription,
        bool $atPeriodEnd = true
    ): bool;

    public function resumeSubscription(
        Subscription $subscription,
        array $gatewayParams = []
    ): bool;
}
