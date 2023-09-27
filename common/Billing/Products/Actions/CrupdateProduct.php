<?php

namespace Common\Billing\Products\Actions;

use Common\Auth\Permissions\Traits\SyncsPermissions;
use Common\Billing\Gateways\Actions\SyncProductOnEnabledGateways;
use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CrupdateProduct
{
    use SyncsPermissions;

    public function execute(
        array $data,
        Product $originalProduct = null,
        $syncProduct = true,
    ): Product {
        $product =
            $originalProduct?->load('prices') ?:
            app(Product::class)->newModelInstance([
                'uuid' => Str::uuid(),
            ]);

        $newData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'hidden' => $data['hidden'] ?? false,
            'free' => $data['free'] ?? false,
            'recommended' => $data['recommended'] ?? false,
            'position' => $data['position'] ?? 0,
            'available_space' => $data['available_space'] ?? null,
            'feature_list' => $data['feature_list'] ?? [],
        ];

        $product = $product->fill($newData);
        $product->save();

        if ($permissions = Arr::get($data, 'permissions')) {
            $this->syncPermissions($product, $permissions);
        }

        $prices = Arr::get($data, 'prices') ?? [];

        // delete old prices
        $originalProduct?->prices->each(function (Price $price) use ($prices) {
            if (
                !Arr::first(
                    $prices,
                    fn($p) => isset($p['id']) && $p['id'] === $price->id,
                )
            ) {
                $price->delete();
            }
        });

        // update existing prices and create new ones
        foreach ($prices as $price) {
            $isExistingPrice = isset($price['id']);
            $pricePayload = [
                'amount' => $price['amount'],
                'interval_count' => $price['interval_count'],
                'interval' => $price['interval'],
                'currency' => $price['currency'],
            ];

            // existing prices can't be updated for existing products, if it has active subscribers. We can add new price though.
            if ($isExistingPrice && $originalProduct?->subscriptions_count) {
                continue;
            }

            if ($isExistingPrice) {
                Price::where('id', $price['id'])->update($pricePayload);
            } else {
                $product->prices()->create($pricePayload);
            }
        }

        if (!$product->free && $syncProduct) {
            app(SyncProductOnEnabledGateways::class)->execute($product);
        }

        return $product;
    }
}
