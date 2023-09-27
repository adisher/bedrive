<?php namespace Common\Billing\Gateways\Stripe;

use App\User;
use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Subscription;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

class StripeSubscriptions
{
    public function __construct(public StripeClient $client)
    {
    }

    public function createPartial(Product $product, User $user): string
    {
        $price = $product->prices->first();

        $user = $this->syncStripeCustomer($user);

        // find incomplete subscriptions for this customer and price
        $stripeSubscription = $this->client->subscriptions
            ->all([
                'customer' => $user->stripe_id,
                'price' => $price->stripe_id,
                'status' => 'incomplete',
                'expand' => ['data.latest_invoice.payment_intent'],
            ])
            ->first();

        // if matching subscription was not created yet, do it now
        if (!$stripeSubscription) {
            $stripeSubscription = $this->client->subscriptions->create([
                'customer' => $user->stripe_id,
                'items' => [
                    [
                        'price' => $price->stripe_id,
                    ],
                ],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);
        }

        // return client secret, needed in frontend to complete subscription
        return $stripeSubscription
            ->latest_invoice->payment_intent->client_secret;
    }

    public function cancel(
        Subscription $subscription,
        bool $atPeriodEnd = true,
    ): bool {
        if (!$subscription->user->stripe_id) {
            return true;
        }

        $stripeSubscription = $this->client->subscriptions->retrieve(
            $subscription->gateway_id,
        );

        // cancel subscription at current period end and don't delete
        if ($atPeriodEnd) {
            $updatedSubscription = $this->client->subscriptions->update(
                $stripeSubscription->id,
                [
                    'cancel_at_period_end' => true,
                ],
            );
            return $updatedSubscription->cancel_at_period_end;
            // cancel and delete subscription instantly
        } else {
            $stripeSubscription = $this->client->subscriptions->cancel(
                $stripeSubscription->id,
            );
            return $stripeSubscription->status === 'cancelled';
        }
    }

    public function resume(Subscription $subscription, array $params): bool
    {
        $stripeSubscription = $this->client->subscriptions->retrieve(
            $subscription->gateway_id,
        );

        $updatedSubscription = $this->client->subscriptions->update(
            $stripeSubscription->id,
            array_merge(
                [
                    'cancel_at_period_end' => false,
                ],
                $params,
            ),
        );

        return $updatedSubscription->status === 'active';
    }

    public function changePlan(
        Subscription $subscription,
        Product $newProduct,
        Price $newPrice,
    ): bool {
        $stripeSubscription = $this->client->subscriptions->retrieve(
            $subscription->gateway_id,
        );

        $updatedSubscription = $this->client->subscriptions->update(
            $stripeSubscription->id,
            [
                'proration_behavior' => 'always_invoice',
                'items' => [
                    [
                        'id' => $stripeSubscription->items->data[0]->id,
                        'price' => $newPrice->stripe_id,
                    ],
                ],
            ],
        );

        return $updatedSubscription->status === 'active';
    }

    protected function syncStripeCustomer(User $user): User
    {
        // make sure user with stored stripe ID actually exists on stripe
        if ($user->stripe_id) {
            try {
                $this->client->customers->retrieve($user->stripe_id);
            } catch (InvalidRequestException $e) {
                $user->stripe_id = null;
            }
        }

        // create customer object on stripe, if it does not exist already
        if (!$user->stripe_id) {
            $customer = $this->client->customers->create([
                'email' => $user->email,
                'metadata' => [
                    'userId' => $user->id,
                ],
            ]);
            $user->fill(['stripe_id' => $customer->id])->save();
        }

        return $user;
    }
}
