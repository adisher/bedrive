<?php

namespace Database\Factories;

use App\File;
use Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->slug.'.'.$this->faker->fileExtension,
            'description' => $this->faker->realTextBetween(500, 1000),
            'file_name' => 's5d8w4w5d8w4w5w5w',
            'mime' => $this->faker->mimeType,
            'file_size' => 159556,
            'type' => Arr::random(['audio', 'video', 'text', 'archive', 'generic']),
        ];
    }
}
