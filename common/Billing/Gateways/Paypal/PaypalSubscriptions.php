<?php namespace Common\Billing\Gateways\Paypal;

use Common\Billing\GatewayException;
use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Subscription;
use Illuminate\Support\Carbon;

class PaypalSubscriptions
{
    use InteractsWithPaypalRestApi;

    public function changePlan(
        Subscription $subscription,
        Product $newProduct,
        Price $newPrice,
    ): bool {
        $response = $this->paypal()->post(
            "billing/subscriptions/$subscription->gateway_id/revise",
            [
                'plan_id' => $newPrice->paypal_id,
            ],
        );

        if (!$response->successful()) {
            throw new GatewayException(__('Could not change plan on PayPal'));
        }

        return $response->successful();
    }

    public function cancel(
        Subscription $subscription,
        $atPeriodEnd = true,
    ): bool {
        if ($atPeriodEnd) {
            $response = $this->paypal()->post(
                "billing/subscriptions/$subscription->gateway_id/suspend",
                ['reason' => 'User requested cancellation.'],
            );
        } else {
            $response = $this->paypal()->post(
                "billing/subscriptions/$subscription->gateway_id/cancel",
                ['reason' => 'Subscription deleted locally.'],
            );
        }

        if (!$response->successful()) {
            throw new GatewayException(
                'Could not cancel subscription on PayPal',
            );
        }

        return true;
    }

    public function resume(Subscription $subscription, array $params): bool
    {
        $response = $this->paypal()->get(
            "billing/subscriptions/$subscription->gateway_id/activate",
            ['reason' => 'Subscription resumed by user.'],
        );

        if (!$response->successful()) {
            throw new GatewayException(
                'Could not resume subscription on PayPal',
            );
        }

        return true;
    }

    public function find(Subscription $subscription)
    {
        $response = $this->paypal()->get(
            "billing/subscriptions/$subscription->gateway_id",
        );

        if (!$response->successful()) {
            throw new GatewayException(
                "Could not find paypal subscription: {$response->json()}",
            );
        }

        return [
            'renews_at' => Carbon::parse(
                $response['billing_info']['next_billing_time'],
            ),
        ];
    }
}
