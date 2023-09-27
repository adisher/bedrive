<?php

namespace Database\Seeders;

use Common\Billing\Models\Product;
use Common\Billing\Products\Actions\CrupdateProduct;
use Illuminate\Database\Seeder;

class SeedDemoProducts extends Seeder
{
    public function run()
    {
        if (Product::count() || !config('common.site.demo')) {
            return;
        }

        app(CrupdateProduct::class)->execute([
            'name' => '50GB',
            'description' => '50GB of space for secure storage',
            'position' => 1,
            'available_space' => 53687091200,
            'prices' => [
                [
                    'amount' => 10,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 1,
                ],
                [
                    'amount' => 54,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 6,
                ],
                [
                    'amount' => 96,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 12,
                ],
            ],
        ]);

        app(CrupdateProduct::class)->execute([
            'name' => '100GB',
            'description' => '100GB of space for secure storage',
            'position' => 2,
            'available_space' => 107374182400,
            'recommended' => true,
            'prices' => [
                [
                    'amount' => 15,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 1,
                ],
                [
                    'amount' => 81,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 6,
                ],
                [
                    'amount' => 144,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 12,
                ],
            ],
        ]);

        app(CrupdateProduct::class)->execute([
            'name' => '300GB',
            'description' => '300GB of space for secure storage',
            'position' => 3,
            'available_space' => 214748364800,
            'prices' => [
                [
                    'amount' => 20,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 1,
                ],
                [
                    'amount' => 135,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 6,
                ],
                [
                    'amount' => 240,
                    'currency' => 'USD',
                    'interval' => 'month',
                    'interval_count' => 12,
                ],
            ],
        ]);
    }
}
