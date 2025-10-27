<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un client OAuth pour l'application bancaire
        DB::table('oauth_clients')->insert([
            'id' => 3,
            'user_id' => null,
            'name' => 'Banque Application Client',
            'secret' => 'secret_banque_app_client_123456789',
            'provider' => null,
            'redirect' => 'http://localhost:8000/callback',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un client pour les accès personnels (admin)
        DB::table('oauth_clients')->insert([
            'id' => 4,
            'user_id' => null,
            'name' => 'Banque Admin Personal Access',
            'secret' => 'secret_admin_personal_987654321',
            'provider' => null,
            'redirect' => 'http://localhost:8000/callback',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('OAuth clients créés avec succès!');
        $this->command->info('Client ID: 3 - Banque Application Client');
        $this->command->info('Client ID: 4 - Banque Admin Personal Access');
    }
}