<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use App\Services\CompteService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class CompteController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister tous les comptes actifs",
     *     description="Récupère la liste des comptes non archivés avec filtrage avancé et pagination",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="page", in="query", description="Numéro de page (défaut: 1)", @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="limit", in="query", description="Nombre d'éléments par page (défaut: 10, max: 100)", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="type", in="query", description="Filtrer par type de compte", @OA\Schema(type="string", enum={"Epargne","Courant","Cheque"})),
     *     @OA\Parameter(name="statut", in="query", description="Filtrer par statut du compte", @OA\Schema(type="string", enum={"actif","inactif","bloque","suspendu"})),
     *     @OA\Parameter(name="search", in="query", description="Recherche textuelle sur titulaire et numéro de compte", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", description="Champ de tri", @OA\Schema(type="string", enum={"date_creation","solde","titulaire","numero_compte"})),
     *     @OA\Parameter(name="order", in="query", description="Ordre de tri", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Parameter(name="client_id", in="query", description="Filtrer par ID client (pour les admins)", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Liste des comptes récupérée avec succès"),
     *     @OA\Response(response=422, description="Paramètres de validation invalides"),
     *     @OA\Response(response=500, description="Erreur interne du serveur")
     * )
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate(\App\Models\Scopes\CompteQueryScope::getValidationRules());

            $query = Compte::query();
            $comptes = \App\Models\Scopes\CompteQueryScope::applyFiltersToQuery($query, $validated)
                ->paginate($validated['limit'] ?? 10);

            $pagination = $this->formatPagination($comptes);
            $links = $this->formatPaginationLinks($comptes, '/api/v1/comptes', $request->query());

            return $this->successResponse(
                CompteResource::collection($comptes),
                'Liste des comptes récupérée avec succès',
                200,
                array_merge($pagination, ['links' => $links])
            );
        } catch (\Throwable $e) {
            Log::error('Erreur dans index comptes: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un nouveau compte bancaire",
     *     description="Crée un nouveau compte bancaire pour un client. Si le client n'existe pas, il est créé automatiquement.",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"type","soldeInitial","devise","client"},
     *         @OA\Property(property="type", type="string", enum={"cheque","epargne","courant"}, description="Type de compte"),
     *         @OA\Property(property="soldeInitial", type="number", minimum=10000, description="Solde initial minimum 10 000"),
     *         @OA\Property(property="devise", type="string", default="XOF", description="Devise du compte"),
     *         @OA\Property(property="client", type="object", required={"titulaire","email","telephone","adresse"},
     *             @OA\Property(property="titulaire", type="string", description="Nom complet du titulaire"),
     *             @OA\Property(property="email", type="string", format="email", description="Email du client"),
     *             @OA\Property(property="telephone", type="string", description="Numéro de téléphone sénégalais"),
     *             @OA\Property(property="adresse", type="string", description="Adresse du client"),
     *             @OA\Property(property="nci", type="string", nullable=true, description="Numéro de carte d'identité")
     *         )
     *     )),
     *     @OA\Response(response=201, description="Compte créé avec succès"),
     *     @OA\Response(response=400, description="Erreur de validation"),
     *     @OA\Response(response=500, description="Erreur interne du serveur")
     * )
     */
    public function store(StoreCompteRequest $request)
    {
        try {
            $validated = $request->validated();

            $compteService = new CompteService();
            $compte = $compteService->createCompte($validated);

            return $this->successResponse(
                new CompteResource($compte),
                'Compte créé avec succès',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Erreur de validation', 422, $e->errors());
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la création du compte: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Récupérer un compte spécifique",
     *     description="Permet à l'admin de récupérer n'importe quel compte par ID, ou au client de récupérer un de ses comptes. Utilise une stratégie de recherche hybride (local + serverless).",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="compteId", in="path", required=true, description="ID du compte", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détails du compte récupérés avec succès", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Détails du compte récupérés avec succès"),
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *             @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *             @OA\Property(property="type", type="string", example="epargne"),
     *             @OA\Property(property="solde", type="number", example=1250000),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *             @OA\Property(property="statut", type="string", example="bloque"),
     *             @OA\Property(property="motifBlocage", type="string", example="Inactivité de 30+ jours"),
     *             @OA\Property(property="metadata", type="object",
     *                 @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                 @OA\Property(property="version", type="integer", example=1)
     *             )
     *         )
     *     )),
     *     @OA\Response(response=404, description="Compte non trouvé", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=false),
     *         @OA\Property(property="error", type="object",
     *             @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *             @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *             @OA\Property(property="details", type="object",
     *                 @OA\Property(property="compteId", type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *             )
     *         )
     *     )),
     *     @OA\Response(response=500, description="Erreur interne du serveur")
     * )
     */
    public function show(string $id)
    {
        try {
            $compteService = new CompteService();
            $compte = $compteService->findCompteWithStrategy($id);

            if (!$compte) {
                throw new \App\Exceptions\CompteNotFoundException($id);
            }

            return $this->successResponse(
                new CompteResource($compte),
                'Détails du compte récupérés avec succès'
            );
        } catch (\App\Exceptions\CompteNotFoundException $e) {
            return $e->render(request());
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la récupération du compte: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comptes/{id}",
     *     summary="Mettre à jour les informations d'un compte bancaire",
     *     description="Met à jour le solde, le type ou le statut d'un compte bancaire",
     *     operationId="updateCompteBank",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID du compte", @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="solde", type="number", example=25000),
     *         @OA\Property(property="statut", type="string", example="actif"),
     *         @OA\Property(property="type", type="string", example="cheque")
     *     )),
     *     @OA\Response(response=200, description="Compte mis à jour avec succès"),
     *     @OA\Response(response=404, description="Compte non trouvé"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function update(Request $request, string $id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->errorResponse('Compte non trouvé', 404);
        }

        $validated = $request->validate([
            'type' => 'sometimes|required|in:cheque,epargne,courant',
            'solde' => 'sometimes|required|numeric|min:0',
            'statut' => 'sometimes|required|in:actif,inactif,bloque,suspendu'
        ]);

        // Transformer le type en majuscule pour la base de données
        if (isset($validated['type'])) {
            $validated['type_compte'] = ucfirst(strtolower($validated['type']));
            unset($validated['type']);
        }

        $compte->update($validated);

        return $this->successResponse(
            new CompteResource($compte->fresh()),
            'Compte mis à jour avec succès'
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Modifier les informations du client d'un compte",
     *     description="Met à jour les informations personnelles du client associé à un compte bancaire. Tous les champs sont optionnels mais au moins un champ doit être fourni.",
     *     operationId="updateClientInfo",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="compteId", in="path", required=true, description="ID du compte", @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *         @OA\Property(property="informationsClient", type="object",
     *             @OA\Property(property="telephone", type="string", example="+221771234568"),
     *             @OA\Property(property="email", type="string", example="amadou.diallo@example.com"),
     *             @OA\Property(property="password", type="string", example="nouveauMotDePasse123"),
     *             @OA\Property(property="nci", type="string", example="1234567890123")
     *         )
     *     )),
     *     @OA\Response(response=200, description="Informations client mises à jour avec succès", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *             @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *             @OA\Property(property="type", type="string", example="epargne"),
     *             @OA\Property(property="solde", type="number", example=1250000),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *             @OA\Property(property="statut", type="string", example="bloque"),
     *             @OA\Property(property="metadata", type="object",
     *                 @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-10-19T11:00:00Z"),
     *                 @OA\Property(property="version", type="integer", example=1)
     *             )
     *         )
     *     )),
     *     @OA\Response(response=404, description="Compte non trouvé"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function updateClientInfo(\App\Http\Requests\UpdateCompteRequest $request, string $id)
    {
        try {
            $compte = Compte::find($id);
            if (!$compte) {
                return $this->errorResponse('Compte non trouvé', 404);
            }

            $validated = $request->validated();

            $compteService = new CompteService();
            $compte = $compteService->updateCompte($compte, $validated);

            return $this->successResponse(
                new CompteResource($compte),
                'Compte mis à jour avec succès'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Erreur de validation', 422, $e->errors());
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la mise à jour du compte: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/archived",
     *     summary="Lister les comptes archivés (soft delete)",
     *     tags={"Comptes"},
     *     @OA\Response(response=200, description="Liste des comptes archivés récupérée avec succès")
     * )
     */
    public function archived(Request $request)
    {
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100'
        ]);

        $perPage = $validated['limit'] ?? 10;
        $comptes = Compte::onlyTrashed()->paginate($perPage);

        $pagination = $this->formatPagination($comptes);
        $links = $this->formatPaginationLinks($comptes, '/api/v1/comptes/archived', $request->query());

        return $this->successResponse(
            CompteResource::collection($comptes),
            'Liste des comptes archivés récupérée avec succès',
            200,
            array_merge($pagination, ['links' => $links])
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Supprimer (archiver) un compte",
     *     description="Supprime un compte bancaire en effectuant un soft delete. Le compte sera marqué comme fermé avec une date de fermeture.",
     *     operationId="deleteCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(name="compteId", in="path", required=true, description="ID du compte à supprimer", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Compte supprimé avec succès", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *             @OA\Property(property="statut", type="string", example="ferme"),
     *             @OA\Property(property="dateFermeture", type="string", format="date-time", example="2025-10-19T11:15:00Z")
     *         )
     *     )),
     *     @OA\Response(response=404, description="Compte non trouvé"),
     *     @OA\Response(response=500, description="Erreur interne du serveur")
     * )
     */
    public function destroy(string $id)
    {
        try {
            $compte = Compte::find($id);
            if (!$compte) {
                return $this->errorResponse('Compte non trouvé', 404);
            }

            // Effectuer le soft delete
            $compte->delete();

            // Retourner les données du compte supprimé avec le nouveau statut
            $responseData = [
                'id' => $compte->id_compte,
                'numeroCompte' => $compte->numero_compte,
                'statut' => 'ferme',
                'dateFermeture' => now()->toISOString()
            ];

            return $this->successResponse(
                $responseData,
                'Compte supprimé avec succès'
            );
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la suppression du compte: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{id}/transactions",
     *     summary="Lister les transactions d'un compte",
     *     tags={"Comptes"},
     *     @OA\Response(response=200, description="Transactions récupérées avec succès")
     * )
     */
    public function transactions(string $id)
    {
        $compte = Compte::find($id);
        if (!$compte) {
            return $this->errorResponse('Compte non trouvé', 404);
        }

        // Placeholder en attendant le modèle Transaction
        return $this->successResponse([], 'Transactions du compte récupérées avec succès');
    }
}
