<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un admin
        $adminId = DB::table('users')->insertGetId([
            'email' => 'loyce99@example.org',
            'mot_de_passe' => Hash::make('password123'),
            'type_user' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer l'entrée correspondante dans la table admins
        DB::table('admins')->insert([
            'id_user' => $adminId,
            'nom' => 'Admin',
            'prenom' => 'Principal',
            'role' => 'super_admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un client
        $clientId = DB::table('users')->insertGetId([
            'email' => 'client@example.com',
            'mot_de_passe' => Hash::make('password123'),
            'type_user' => 'client',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer l'entrée correspondante dans la table clients
        DB::table('clients')->insert([
            'id_user' => $clientId,
            'nom' => 'Client',
            'prenom' => 'Test',
            'telephone' => '+221771234567',
            'adresse' => 'Dakar, Sénégal',
            'nci' => '1234567890123',
            'code' => 'CLIENT001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Utilisateurs de test créés avec succès!');
        $this->command->info('Admin: loyce99@example.org / password123');
        $this->command->info('Client: client@example.com / password123');
    }
}