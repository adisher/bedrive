<?php

namespace Common\Workspaces;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'owner_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}
