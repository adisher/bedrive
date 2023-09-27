<?php

namespace Common\Domains;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CustomDomainFactory extends Factory
{
    protected $model = CustomDomain::class;

    public function definition(): array
    {
        return [
            'host' => $this->faker->unique()->domainName,
            'global' => false,
            'resource_id' => null,
            'resource_type' => null,
            'workspace_id' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'user_id' => 1,
        ];
    }
}
