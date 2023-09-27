<?php

namespace Common\Billing\Gateways;

use Common\Billing\GatewayException;
use Common\Billing\Gateways\Actions\SyncProductOnEnabledGateways;
use Common\Billing\Models\Product;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SyncProductsController extends BaseController
{
    /**
     * Sync local plans with 3rd party gateways
     */
    public function syncProducts(): Response|JsonResponse
    {
        $products = Product::where('free', false)->get();

        foreach ($products as $product) {
            try {
                app(SyncProductOnEnabledGateways::class)->execute($product);
            } catch (GatewayException $e) {
                return $this->error(
                    "Could not sync \"$product->name\" product: {$e->getMessage()}",
                );
            }
        }

        return $this->success();
    }
}
