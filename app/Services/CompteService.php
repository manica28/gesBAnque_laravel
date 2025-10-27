<?php

namespace App\Services;

use App\Models\User;
use App\Models\Client;
use App\Models\Compte;
use App\Events\ClientNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompteService
{
    /**
     * Créer un compte bancaire avec gestion automatique du client.
     */
    public function createCompte(array $data): Compte
    {
        return DB::transaction(function () use ($data) {
            // Trouver ou créer le client
            $client = $this->findOrCreateClient($data['client']);

            // Créer le compte bancaire
            $compte = $this->createCompteRecord($client, $data);

            // Déclencher l'événement de création du compte
            \App\Events\CompteCreated::dispatch($compte);

            return $compte;
        });
    }

    /**
     * Trouver un client existant ou en créer un nouveau.
     */
    private function findOrCreateClient(array $clientData): Client
    {
        // Vérifie si un utilisateur existe avec cet email ou téléphone
        $existingUser = User::where('email', $clientData['email'])
            ->orWhere('telephone', $clientData['telephone'])
            ->first();

        if ($existingUser) {
            // Si le client correspondant n'existe pas encore, on le crée
            $client = Client::firstOrCreate(
                ['id_user' => $existingUser->id_user],
                ['solde_initial' => 0]
            );

            return $client;
        }

        // Sinon on crée un nouvel utilisateur et son client
        return $this->createNewClient($clientData);
    }

    /**
     * Créer un nouvel utilisateur et un client lié.
     */
    private function createNewClient(array $clientData): Client
    {
        $password = $this->generatePassword();
        $verificationCode = $this->generateVerificationCode();

        // Créer un utilisateur
        $user = User::create([
            'nom' => $this->extractFirstName($clientData['titulaire']),
            'prenom' => $this->extractLastName($clientData['titulaire']),
            'email' => $clientData['email'],
            'telephone' => $clientData['telephone'],
            'adresse' => $clientData['adresse'],
            'mot_de_passe' => bcrypt($password),
            'type_user' => 'client',
            'statut' => 'actif',
        ]);

        // Créer le client lié avec les nouvelles informations
        $client = Client::create([
            'id_user' => $user->id_user,
            'nci' => $clientData['nci'] ?? null,
            'email' => $clientData['email'],
            'telephone' => $clientData['telephone'],
            'adresse' => $clientData['adresse'],
            'titulaire' => $clientData['titulaire'],
            'password' => $password, // Stocker le mot de passe en clair pour l'email
            'code' => $verificationCode, // Code pour première connexion
            'solde_initial' => 0,
        ]);

        // Notifier le client (email ou SMS par exemple)
        ClientNotification::dispatch($client, $password, $verificationCode);

        return $client;
    }

    /**
     * Créer un compte bancaire pour un client.
     */
    private function createCompteRecord(Client $client, array $data): Compte
    {
        return Compte::create([
            'numero_compte' => $this->generateNumeroCompte(),
            'id_client' => $client->id_client,
            'titulaire' => $data['client']['titulaire'],
            'type_compte' => ucfirst($data['type']),
            'solde' => $data['soldeInitial'],
            'statut' => 'actif',
            'devise' => strtoupper($data['devise']),
            'metadata' => [
                'solde_initial' => $data['soldeInitial'],
                'date_creation' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Génère un numéro de compte unique (ex: CPT123456).
     */
    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'CPT' . rand(100000, 999999);
        } while (Compte::where('numero_compte', $numero)->exists());

        return $numero;
    }

    /**
     * Génère un mot de passe aléatoire pour un nouveau client.
     */
    private function generatePassword(): string
    {
        return Str::random(12);
    }

    /**
     * Génère un code de vérification à 6 chiffres.
     */
    private function generateVerificationCode(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Extrait le prénom du nom complet.
     */
    private function extractFirstName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? $fullName;
    }

    /**
     * Extrait le nom de famille du nom complet.
     */
    private function extractLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }

    /**
     * Met à jour les informations d'un compte et de son client.
     */
    public function updateCompte(Compte $compte, array $data): Compte
    {
        return DB::transaction(function () use ($compte, $data) {
            // Mettre à jour le titulaire du compte si fourni
            if (isset($data['titulaire'])) {
                $compte->update(['titulaire' => $data['titulaire']]);
            }

            // Mettre à jour les informations client si fournies
            if (isset($data['informationsClient']) && !empty($data['informationsClient'])) {
                $this->updateClientInformation($compte->client, $data['informationsClient']);
            }

            // Mettre à jour les métadonnées
            $metadata = $compte->metadata ?? [];
            $metadata['derniereModification'] = now()->toISOString();
            $metadata['version'] = ($metadata['version'] ?? 0) + 1;

            $compte->update(['metadata' => $metadata]);

            return $compte->fresh(); // Recharger le compte avec les relations
        });
    }

    /**
     * Met à jour les informations du client (utilisateur lié).
     */
    private function updateClientInformation(Client $client, array $clientData): void
    {
        $userData = [];

        // Préparer les données utilisateur
        if (isset($clientData['telephone'])) {
            $userData['telephone'] = $clientData['telephone'];
        }

        if (isset($clientData['email'])) {
            $userData['email'] = $clientData['email'];
        }

        if (isset($clientData['password'])) {
            $userData['mot_de_passe'] = bcrypt($clientData['password']);
        }

        // Mettre à jour l'utilisateur si des données sont fournies
        if (!empty($userData)) {
            $client->user->update($userData);
        }

        // Mettre à jour le client (NCI)
        $clientUpdateData = [];
        if (isset($clientData['nci'])) {
            $clientUpdateData['nci'] = $clientData['nci'];
        }

        if (!empty($clientUpdateData)) {
            $client->update($clientUpdateData);
        }
    }

    /**
     * Recherche hybride d'un compte (local + serverless).
     *
     * Stratégie :
     * - Par défaut : recherche en local pour comptes chèque ou épargne actifs
     * - Si non trouvé : recherche serverless
     */
    public function findCompteWithStrategy(string $compteId): ?Compte
    {
        // Recherche en local d'abord
        $compte = Compte::find($compteId);

        // Si trouvé et que c'est un compte chèque ou épargne actif, retourner directement
        if ($compte && in_array($compte->type_compte, ['Cheque', 'Epargne']) && $compte->statut === 'actif') {
            return $compte;
        }

        // Si non trouvé en local ou compte non éligible, rechercher en serverless
        if (!$compte) {
            return $this->searchInServerless($compteId);
        }

        // Retourner le compte trouvé en local même s'il n'est pas éligible
        return $compte;
    }

    /**
     * Recherche dans la base serverless (simulation).
     * En production, ceci ferait appel à une API externe ou base de données distante.
     */
    private function searchInServerless(string $compteId): ?Compte
    {
        // Simulation de recherche serverless
        // En production, remplacer par un appel API réel

        // Pour l'instant, retourner null (pas trouvé)
        // Dans un vrai système, ceci pourrait être :
        // - Un appel HTTP vers une API serverless
        // - Une requête vers une base de données distante
        // - Un appel vers un service de cache distribué

        Log::info("Recherche serverless pour le compte: {$compteId}");

        // Simulation d'un délai réseau
        usleep(100000); // 100ms

        return null; // Pas trouvé en serverless pour cette simulation
    }
}
