<?php namespace Common\Billing\Gateways\Paypal;

use App\User;
use Common\Billing\Gateways\Contracts\CommonSubscriptionGatewayActions;
use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Subscription;
use Common\Settings\Settings;

class Paypal implements CommonSubscriptionGatewayActions
{
    use InteractsWithPaypalRestApi;

    public function __construct(
        protected Settings $settings,
        protected PaypalPlans $plans,
        protected PaypalSubscriptions $subscriptions,
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) app(Settings::class)->get('billing.paypal.enable');
    }

    public function syncPlan(Product $product): bool
    {
        return $this->plans->sync($product);
    }

    public function deletePlan(Product $product): bool
    {
        return $this->plans->delete($product);
    }

    public function storeSubscriptionDetailsLocally(
        string $paypalSubscriptionId,
        User $user,
    ): bool {
        $response = $this->paypal()->get(
            "billing/subscriptions/$paypalSubscriptionId",
        );

        if ($response->successful() && $response['status'] === 'ACTIVE') {
            $price = Price::where(
                'paypal_id',
                $response['plan_id'],
            )->firstOrFail();
            if (!$user->paypal_id) {
                $user
                    ->fill(['paypal_id' => $response['subscriber']['payer_id']])
                    ->save();
            }
            $user->subscribe('paypal', $response['id'], $price);
            return true;
        }

        return false;
    }

    public function changePlan(
        Subscription $subscription,
        Product $newProduct,
        Price $newPrice,
    ): bool {
        return $this->subscriptions->changePlan(
            $subscription,
            $newProduct,
            $newPrice,
        );
    }

    public function cancelSubscription(
        Subscription $subscription,
        bool $atPeriodEnd = true,
    ): bool {
        return $this->subscriptions->cancel($subscription, $atPeriodEnd);
    }

    public function resumeSubscription(
        Subscription $subscription,
        array $gatewayParams = [],
    ): bool {
        return $this->subscriptions->resume($subscription, $gatewayParams);
    }

    public function findSubscription(Subscription $subscription): array
    {
        return $this->subscriptions->find($subscription);
    }
}
