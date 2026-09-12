<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory()->state(['type' => Client::TYPE_LOCATION]),
            'location' => $this->faker->city().', '.$this->faker->stateAbbr(),
        ];
    }
}
