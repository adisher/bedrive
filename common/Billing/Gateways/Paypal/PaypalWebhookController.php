<?php namespace Common\Billing\Gateways\Paypal;

use App\User;
use Common\Billing\GatewayException;
use Common\Billing\Invoices\CreateInvoice;
use Common\Billing\Notifications\PaymentFailed;
use Common\Billing\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class PaypalWebhookController extends Controller
{
    use InteractsWithPaypalRestApi;

    public function __construct(
        protected Subscription $subscription,
        protected Paypal $paypal,
    ) {
    }

    public function handleWebhook(Request $request): Response
    {
        $payload = $request->all();

        if (
            config('common.site.verify_paypal_webhook') &&
            !$this->webhookIsValid()
        ) {
            return response('Webhook validation failed', 422);
        }

        switch ($payload['event_type']) {
            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                return $this->handleInvoicePaymentFailed($payload);
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                return $this->handleSubscriptionCreated($payload);
            case 'BILLING.SUBSCRIPTION.CANCELLED':
            case 'BILLING.SUBSCRIPTION.EXPIRED':
                return $this->handleSubscriptionCancelledOrExpired($payload);
            case 'PAYMENT.SALE.COMPLETED':
                return $this->handleSaleCompleted($payload);
            default:
                return response('Webhook Handled', 200);
        }
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $paypalSubscriptionId = Arr::get(
            $payload,
            'resource.billing_agreement_id',
        );

        $subscription = $this->subscription
            ->where('gateway_id', $paypalSubscriptionId)
            ->first();
        $subscription?->user->notify(new PaymentFailed($subscription));

        return response('Webhook handled', 200);
    }

    protected function handleSubscriptionCancelledOrExpired(
        array $payload,
    ): Response {
        $paypalSubscriptionId = $payload['resource']['id'];

        $subscription = $this->subscription
            ->where('gateway_id', $paypalSubscriptionId)
            ->first();

        if ($subscription && !$subscription->cancelled()) {
            $subscription->markAsCancelled();
        }

        return response('Webhook Handled', 200);
    }

    protected function handleSaleCompleted(array $payload): Response
    {
        $gatewayId = Arr::get($payload, 'resource.billing_agreement_id');

        $subscription = $this->subscription
            ->where('gateway_id', $gatewayId)
            ->first();

        if ($subscription) {
            $paypalSubscription = $this->gateway
                ->subscriptions()
                ->find($subscription);
            $subscription
                ->fill(['renews_at' => $paypalSubscription['renews_at']])
                ->save();
            app(CreateInvoice::class)->execute([
                'subscription_id' => $subscription->id,
                'paid' => true,
            ]);
        }

        return response('Webhook Handled', 200);
    }

    protected function handleSubscriptionCreated(array $payload): Response
    {
        $paypalSubscriptionId = Arr::get($payload, 'resource.id');
        $paypalUserId = Arr::get($payload, 'resource.subscriber.payer_id');

        $user = User::where('paypal_id', $paypalUserId)->first();
        if ($user) {
            $this->paypal->storeSubscriptionDetailsLocally(
                $paypalSubscriptionId,
                $user,
            );
        }

        return response('Webhook Handled', 200);
    }

    protected function webhookIsValid(): bool
    {
        $payload = [
            'auth_algo' => request()->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => request()->header('PAYPAL-CERT-URL'),
            'transmission_id' => request()->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => request()->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => request()->header(
                'PAYPAL-TRANSMISSION-TIME',
            ),
            'webhook_id' => config('services.paypal.webhook_id'),
            'webhook_event' => request()->all(),
        ];

        $response = $this->paypal()->post(
            'notifications/verify-webhook-signature',
            $payload,
        );

        if (!$response->successful()) {
            throw new GatewayException(
                "Could not validate paypal webhook: {$response->body()}",
            );
        }

        return $response['verification_status'] === 'SUCCESS';
    }
}
