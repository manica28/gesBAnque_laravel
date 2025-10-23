<?php

namespace Database\Factories;
use App\Models\Compte;
use Illuminate\Database\Eloquent\Factories\Factory;


class CompteFactory extends Factory
{
   
    public function definition(): array
    {
        return [
            'id_client' => \App\Models\Client::factory()->create()->id_client,
            'titulaire' => $this->faker->name,
            'type_compte' => $this->faker->randomElement(['Epargne', 'Courant', 'Cheque']),
            'solde' => $this->faker->numberBetween(1000, 100000),
            'statut' => $this->faker->randomElement(['actif', 'inactif', 'bloque', 'suspendu']),
        ];
    }
}
