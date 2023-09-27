<?php namespace Common\Billing\Gateways\Stripe;

use App\User;
use Common\Billing\Gateways\Contracts\CommonSubscriptionGatewayActions;
use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Subscription;
use Common\Settings\Settings;
use Stripe\StripeClient;

class Stripe implements CommonSubscriptionGatewayActions
{
    public StripePlans $plans;
    public StripeSubscriptions $subscriptions;
    public StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient([
            'api_key' => config('services.stripe.secret'),
            'stripe_version' => '2022-08-01',
        ]);

        $this->plans = new StripePlans($this->client);
        $this->subscriptions = new StripeSubscriptions($this->client);
    }

    public function isEnabled(): bool
    {
        return (bool) app(Settings::class)->get('billing.stripe.enable');
    }

    public function syncPlan(Product $product): bool
    {
        return $this->plans->sync($product);
    }

    public function getAllPlans(): array
    {
        return $this->plans->getAll();
    }

    public function changePlan(
        Subscription $subscription,
        Product $newProduct,
        Price $newPrice
    ): bool {
        return $this->subscriptions->changePlan(
            $subscription,
            $newProduct,
            $newPrice,
        );
    }

    public function deletePlan(Product $product): bool
    {
        return $this->plans->delete($product);
    }

    public function cancelSubscription(
        Subscription $subscription,
        bool $atPeriodEnd = true
    ): bool {
        return $this->subscriptions->cancel($subscription, $atPeriodEnd);
    }

    public function resumeSubscription(
        Subscription $subscription,
        array $gatewayParams = []
    ): bool {
        return $this->subscriptions->resume($subscription, $gatewayParams);
    }

    public function createSetupIntent(User $user): string
    {
        $setupIntent = $this->client->setupIntents->create([
            'customer' => $user->stripe_id,
        ]);

        return $setupIntent->client_secret;
    }

    public function changeDefaultPaymentMethod(
        User $user,
        string $paymentMethodId
    ): bool {
        $updatedUser = $this->client->customers->update($user->stripe_id, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);

        $isSuccess =
            $updatedUser->invoice_settings['default_payment_method'] ==
            $paymentMethodId;

        if ($isSuccess) {
            $paymentMethod = $this->client->paymentMethods->retrieve(
                $paymentMethodId,
            );
            if ($paymentMethod->type === 'card') {
                $this->storeCardDetailsLocally(
                    $user,
                    $paymentMethod->card->toArray(),
                );
            }
        }

        return $isSuccess;
    }

    public function storeCardDetailsLocally(User $user, array $card)
    {
        $user->update([
            'card_brand' => $card['brand'],
            'card_last_four' => $card['last4'],
            'card_expires' => "{$card['exp_month']}/{$card['exp_year']}",
        ]);
    }

    public function storeSubscriptionDetailsLocally(
        string $stripeSubscriptionId
    ) {
        $stripeSubscription = $this->client->subscriptions->retrieve(
            $stripeSubscriptionId,
        );

        $stripePrice = $stripeSubscription->items->data[0]->price;

        if ($stripeSubscription->status === 'active') {
            $user = User::where(
                'stripe_id',
                $stripeSubscription->customer,
            )->firstOrFail();
            $price = Price::where('stripe_id', $stripePrice->id)->firstOrFail();

            $user->subscribe('stripe', $stripeSubscription->id, $price);
        }
    }
}
