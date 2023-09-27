<?php namespace Common\Billing\Gateways\Stripe;

use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Price as StripePrice;
use Stripe\StripeClient;

class StripePlans
{
    use FormatsMoney;

    public function __construct(protected StripeClient $client)
    {
    }

    public function sync(Product $product): bool
    {
        $product->load('prices');

        // create product on stripe, if it does not exist already
        try {
            $stripeProduct = $this->client->products->retrieve($product->uuid);
        } catch (ApiErrorException $err) {
            $stripeProduct = null;
        }

        if (!$stripeProduct) {
            $this->client->products->create([
                'id' => $product->uuid,
                'name' => $product->name,
            ]);
        }

        // create any local product prices on stripe, that don't exist there already
        $product->prices->each(function (Price $price) use ($product) {
            // todo: check if price actually exist on stripe (when switching from test to live will not work otherwise)
            if (!$price->stripe_id) {
                $this->createPrice($product, $price);
            }
        });

        return true;
    }

    public function createPrice(Product $product, Price $price): StripePrice
    {
        $stripePrice = $this->client->prices->create([
            'product' => $product->uuid,
            'unit_amount' => $this->priceToCents($price),
            'currency' => $price->currency,
            'recurring' => [
                'interval' => $price->interval,
                'interval_count' => $price->interval_count,
            ],
        ]);

        $price->fill(['stripe_id' => $stripePrice->id])->save();

        return $stripePrice;
    }

    public function delete(Product $product): bool
    {
        // stripe does not allow deleting product if it has prices attached,
        // and prices can't be deleted via API, we archive the product instead
        try {
            $this->client->products->update($product->uuid, [
                'active' => false,
            ]);
        } catch (InvalidRequestException $e) {
            // if this product is already deleted on stripe, ignore
            if ($e->getStripeCode() !== 'resource_missing') {
                throw $e;
            }
        }
        return true;
    }

    public function getAll(): array
    {
        return $this->client->products->all()->toArray();
    }
}
