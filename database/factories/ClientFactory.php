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
        $user = \App\Models\User::factory()->create(['type_user' => 'client']);
        
        return [
            'id_user' => $user->id_user,
            'nci' => fake()->unique()->numerify('CI########'),
            'email' => $user->email,
            'telephone' => $user->telephone,
            'adresse' => $user->adresse,
            'titulaire' => $user->nom . ' ' . $user->prenom,
            'password' => $user->mot_de_passe,
            'code' => fake()->numerify('####'),
            'solde_initial' => fake()->numberBetween(0, 10000),
        ];
    }
}
