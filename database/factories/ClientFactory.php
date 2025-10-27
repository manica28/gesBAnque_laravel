<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_user' => \App\Models\User::factory()->create(['type_user' => 'client'])->id_user,
            'nci' => fake()->unique()->numerify('CI########'),
            'solde_initial' => fake()->numberBetween(0, 10000),
        ];
    }
}
