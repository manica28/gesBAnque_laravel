<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer 2 admins
        \App\Models\Admin::factory(2)->create();

        // Créer 10 clients avec leurs comptes
        \App\Models\Client::factory(10)->create()->each(function ($client) {
            \App\Models\Compte::factory(1)->create([
                'id_client' => $client->id_client,
            ]);
        });

        // Créer des transactions
        \App\Models\Transaction::factory(50)->create();
    }
}