<?php

use Common\Billing\Models\Price;
use Common\Billing\Models\Product;
use Common\Billing\Subscription;
use Illuminate\Database\Migrations\Migration;

class MoveBillingPlansToProductsAndPricesTables extends Migration
{
    public function up()
    {
        $plans = DB::table('billing_plans')->get();

        $products = [];

        $plans->each(function ($plan) use (&$products) {
            // convert parent plans to products
            if (!$plan->parent_id) {
                $product = Product::create([
                    'name' => $plan->name,
                    'position' => $plan->position,
                    'uuid' => $plan->uuid,
                    'feature_list' => $plan->features
                        ? json_encode($plan->features)
                        : null,
                    'created_at' => $plan->created_at,
                    'updated_at' => $plan->updated_at,
                    'available_space' => $plan->available_space,
                    'free' => $plan->free,
                    'recommended' => $plan->recommended,
                ]);
                $products[$plan->id] = $product;

                // migrate permissions from plan to newly created product
                DB::table('permissionables')
                    ->where('permissionable_id', $plan->id)
                    ->where('permissionable_type', 'Common\Billing\BillingPlan')
                    ->update([
                        'permissionable_id' => $product->id,
                        'permissionable_type' => Product::class,
                    ]);
            }
        });

        // create prices for products from child plans
        $plans->each(function ($plan) use ($products) {
            $productId = $products[$plan->parent_id ?? $plan->id]->id;
            $price = Price::create([
                'amount' => $plan->amount ?? 1,
                'currency' => $plan->currency,
                'interval' => $plan->interval,
                'interval_count' => $plan->interval_count,
                'stripe_id' => $plan->uuid,
                'paypal_id' => $plan->paypal_id,
                'created_at' => $plan->created_at,
                'updated_at' => $plan->updated_at,
                'default' => !$plan->parent_id,
                'product_id' => $products[$plan->parent_id ?? $plan->id]->id,
            ]);

            Subscription::where('price_id', $plan->id)->update([
                'price_id' => $price->id,
                'product_id' => $productId,
            ]);
        });
    }

    public function down()
    {
        //
    }
}
