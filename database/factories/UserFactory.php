<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'id_user' => (string) Str::uuid(),
            'nom' => fake()->firstName(),
            'prenom' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'adresse' => fake()->address(),
            'mot_de_passe' => static::$password ??= Hash::make('password'),
            'type_user' => fake()->randomElement(['client', 'admin']),
            'statut' => fake()->randomElement(['actif', 'inactif', 'suspendu']),
            'date_creation' => now(),
        ];
    }
}
