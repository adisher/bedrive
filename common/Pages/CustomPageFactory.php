<?php

namespace Common\Pages;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class CustomPageFactory extends Factory
{
    protected $model = CustomPage::class;

    public function definition(): array
    {
        $period = CarbonPeriod::create(now()->subMonths(3), now());

        return [
            'body' => $this->faker->randomHtml(),
            'slug' => $this->faker->slug(3),
            'title' => $this->faker->words(3, true),
            'workspace_id' => 0,
            'user_id' => 1,
            'created_at' => Arr::random($period->toArray()),
            'updated_at' => Arr::random($period->toArray()),
        ];
    }
}
