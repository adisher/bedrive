<?php namespace Common\Billing\Gateways\Paypal;

use Common\Billing\GatewayException;
use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Illuminate\Support\Str;

class PaypalPlans
{
    use InteractsWithPaypalRestApi;

    public function sync(Product $product): bool
    {
        $product->load('prices');

        // there's only one global product on PayPal and not one per plan as on stripe
        $productId = config('services.paypal.product_id');
        $response = $this->paypal()->get("catalogs/products/$productId");
        if (!$response->successful()) {
            $this->paypal()->post('catalogs/products', [
                'id' => $productId,
                'name' => config('services.paypal.product_name'),
                'type' => 'DIGITAL',
            ]);
        }

        // create any local product prices (plans) on PayPal, that don't exist there already
        $product->prices->each(function (Price $price) use ($product) {
            if (!$price->paypal_id) {
                $this->create($product, $price);
            }
        });

        return true;
    }

    protected function create(Product $product, Price $price): bool
    {
        $response = $this->paypal()->post('billing/plans', [
            'name' => $product->name,
            'product_id' => config('services.paypal.product_id'),
            'status' => 'ACTIVE',
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'payment_failure_threshold' => 2,
            ],
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => Str::upper($price->interval),
                        'interval_count' => $price->interval_count,
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0, // infinite
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => number_format(
                                $price->amount,
                                2,
                                '.',
                                '',
                            ),
                            'currency_code' => Str::upper($price->currency),
                        ],
                    ],
                ],
            ],
        ]);

        if (!$response->successful()) {
            throw new GatewayException('Could not create plan on PayPal');
        }

        $price->fill(['paypal_id' => $response['id']])->save();
        return true;
    }

    public function delete(Product $product): bool
    {
        $statuses = $product->prices->map(function (Price $price) {
            $response = $this->paypal()->post(
                "billing/plans/{$price->paypal_id}/deactivate",
            );
            return $response->successful();
        });

        return $statuses->every(fn($status) => $status);
    }
}
