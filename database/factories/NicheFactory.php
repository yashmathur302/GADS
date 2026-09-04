<?php

namespace Database\Factories;

use App\Models\Industry;
use App\Models\Niche;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Niche>
 */
class NicheFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'industry_id' => Industry::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
        ];
    }
}
