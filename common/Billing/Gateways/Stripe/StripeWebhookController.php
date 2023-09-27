<?php namespace Common\Billing\Gateways\Stripe;

use App\User;
use Carbon\Carbon;
use Common\Billing\Invoices\CreateInvoice;
use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Notifications\PaymentFailed;
use Common\Billing\Subscription;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected Stripe $stripe,
        protected Subscription $subscription,
    ) {
    }

    public function handleWebhook(Request $request): Response|JsonResponse
    {
        $webhookSecret = config('services.stripe.webhook_secret');
        if ($webhookSecret) {
            try {
                $event = Webhook::constructEvent(
                    $request->getContent(),
                    $request->header('stripe-signature'),
                    $webhookSecret,
                )->toArray();
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
        } else {
            $event = $request->all();
        }

        switch ($event['type']) {
            case 'invoice.paid':
                return $this->handleInvoicePaid($event);
            // sync user payment methods with local database
            case 'customer.updated':
                return $this->handleCustomerUpdated($event);
            // user subscription ended and can't be resumed
            case 'customer.subscription.deleted':
                $id = $event['data']['object']['id'];
                return $this->deleteSubscription($id);
            // user subscribed
            case 'customer.subscription.created':
                return $this->handleSubscriptionCreated($event);
            // automatic subscription renewal failed on stripe
            case 'invoice.payment_failed':
                return $this->handleInvoicePaymentFailed($event);
            case 'customer.subscription.updated':
                return $this->handleSubscriptionUpdated($event);
            default:
                return response('Webhook handled', 200);
        }
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $stripeUserId = $payload['data']['object']['customer'];
        $user = User::where('stripe_id', $stripeUserId)->first();
        if ($user) {
            $stripeSubscription = $user
                ->subscriptions()
                ->where('gateway_name', 'stripe')
                ->first();
            if ($stripeSubscription) {
                $user->notify(new PaymentFailed($stripeSubscription));
            }
        }
        return response('Webhook handled', 200);
    }

    protected function handleInvoicePaid(
        array $payload,
    ): Response|Application|ResponseFactory {
        $stripeInvoice = $payload['data']['object'];
        $stripeSubscriptionId = $stripeInvoice['subscription'];

        $subscription = Subscription::where(
            'gateway_id',
            $stripeSubscriptionId,
        )->first();

        if ($subscription) {
            app(CreateInvoice::class)->execute([
                'subscription_id' => $subscription->id,
                'paid' => true,
            ]);
        } else {
            return response('Wait for subscription to be created', 503);
        }

        return response('Webhook Handled', 200);
    }

    protected function handleCustomerUpdated(
        array $payload,
    ): Response|Application|ResponseFactory {
        $stripeCustomer = $payload['data']['object'];
        $user = User::where('stripe_id', $stripeCustomer['id'])->firstOrFail();

        $stripePaymentMethods = $this->stripe->client->customers
            ->allPaymentMethods($stripeCustomer['id'], ['type' => 'card'])
            ->toArray()['data'];

        if (!empty($stripePaymentMethods)) {
            $card = $stripePaymentMethods[0]['card'];
            $this->stripe->storeCardDetailsLocally($user, $card);
        }

        return response('Webhook Handled', 200);
    }

    protected function handleSubscriptionUpdated(
        array $payload,
    ): Response|Application|ResponseFactory {
        $stripeSubscription = $payload['data']['object'];
        $newStripePrice = $stripeSubscription['items']['data'][0]['price'];

        // find product, price and subscription by stripe ID
        $newPrice = Price::where(
            'stripe_id',
            $newStripePrice['id'],
        )->firstOrFail();
        $newProduct = Product::where(
            'uuid',
            $newStripePrice['product'],
        )->firstOrFail();
        $subscription = Subscription::where(
            'gateway_id',
            $stripeSubscription['id'],
        )->firstOrFail();

        // sync local subscription details with stripe
        $subscription
            ->fill([
                'renews_at' => Carbon::createFromTimestamp(
                    $stripeSubscription['current_period_end'],
                ),
                'product_id' => $newProduct->id,
                'price_id' => $newPrice->id,
            ])
            ->save();

        // mark local subscription as cancelled if renew failed on stripe
        if (
            $stripeSubscription['status'] === 'cancelled' ||
            $stripeSubscription['status'] === 'unpaid' ||
            $stripeSubscription['cancel_at_period_end']
        ) {
            $subscription->markAsCancelled();
        }

        return response('Webhook Handled', 200);
    }

    protected function handleSubscriptionCreated(
        array $payload,
    ): Response|Application|ResponseFactory {
        $stripeSubscription = $payload['data']['object'];

        $this->stripe->storeSubscriptionDetailsLocally(
            $stripeSubscription['id'],
        );

        return response('Webhook Handled', 200);
    }

    protected function markSubscriptionAsCancelled(
        string $stripeSubscriptionId,
    ): Response|Application|ResponseFactory {
        $subscription = Subscription::where(
            'gateway_id',
            $stripeSubscriptionId,
        )->first();

        if ($subscription && !$subscription->cancelled()) {
            $subscription->markAsCancelled();
        }

        return response('Webhook handled', 200);
    }

    protected function deleteSubscription(
        string $stripeSubscriptionId,
    ): Response|Application|ResponseFactory {
        $subscription = Subscription::where(
            'gateway_id',
            $stripeSubscriptionId,
        )->first();

        $subscription?->cancelAndDelete();

        return response('Webhook handled', 200);
    }
}
