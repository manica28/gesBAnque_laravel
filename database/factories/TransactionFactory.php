<?php

namespace Database\Factories;
use App\Models\Transaction;
use App\Models\Compte;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
   
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'id_compte' => Compte::factory(),
            'type_transaction' => $this->faker->randomElement(['depot', 'retrait', 'salaire']),
            'montant' => $this->faker->numberBetween(100, 5000),
            'statut' => $this->faker->randomElement(['success', 'echec']),
            'description' => $this->faker->sentence(),
        ];
    }
}
