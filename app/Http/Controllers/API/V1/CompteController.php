<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page (défaut: 1)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (défaut: 10, max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Epargne", "Courant", "Cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut du compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "inactif", "bloque", "suspendu"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche textuelle sur titulaire et numéro de compte",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"date_creation", "solde", "titulaire", "numero_compte"})
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="client_id",
     *         in="query",
     *         description="Filtrer par ID client (pour les admins)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Liste des comptes récupérée avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Compte")),
     *             @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Paramètres de validation invalides",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Validation des paramètres en utilisant les règles du scope
        $validated = $request->validate(\App\Models\Scopes\CompteQueryScope::getValidationRules());

        // Pour les tests sans authentification, on simule un admin (voit tous les comptes)
        // $user = Auth::user();

        // Construction de la requête avec le scope global
        $query = Compte::query();

        // Appliquer tous les filtres via le scope global
        $comptes = \App\Models\Scopes\CompteQueryScope::applyFiltersToQuery($query, $validated)->paginate(
            $validated['limit'] ?? 10,
            ['*'],
            'page',
            $validated['page'] ?? 1
        );

        // Formatage de la réponse
        $pagination = $this->formatPagination($comptes);
        $links = $this->formatPaginationLinks($comptes, '/api/v1/comptes', $request->query());

        return $this->successResponse(
            CompteResource::collection($comptes),
            'Liste des comptes récupérée avec succès',
            200,
            array_merge($pagination, ['links' => $links])
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un nouveau compte",
     *     description="Crée un nouveau compte bancaire pour un client",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_client", "titulaire", "type_compte", "solde"},
     *             @OA\Property(property="id_client", type="integer", example=1),
     *             @OA\Property(property="titulaire", type="string", example="John Doe"),
     *             @OA\Property(property="type_compte", type="string", enum={"Epargne", "Courant", "Cheque"}, example="Courant"),
     *             @OA\Property(property="solde", type="number", format="float", example=1000.50),
     *             @OA\Property(property="devise", type="string", example="XOF"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "suspendu"}, example="actif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de validation invalides",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_client' => 'required|exists:clients,id_client',
            'titulaire' => 'required|string|max:255',
            'type_compte' => 'required|in:Epargne,Courant,Cheque',
            'solde' => 'required|numeric|min:0',
            'devise' => 'nullable|string|max:3',
            'statut' => 'nullable|in:actif,inactif,bloque,suspendu',
            'motifBlocage' => 'nullable|string|max:500',
            'metadata' => 'nullable|array'
        ]);

        $compte = Compte::create($validated);

        return $this->successResponse(
            new CompteResource($compte),
            'Compte créé avec succès',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{id}",
     *     summary="Afficher un compte spécifique",
     *     description="Récupère les détails d'un compte spécifique",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails du compte récupérés avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(string $id)
    {
        $compte = Compte::findOrFail($id);

        return $this->successResponse(
            new CompteResource($compte),
            'Détails du compte récupérés avec succès'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comptes/{id}",
     *     summary="Mettre à jour un compte",
     *     description="Met à jour les informations d'un compte existant",
     *     operationId="updateCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulaire", type="string", example="Jane Doe"),
     *             @OA\Property(property="type_compte", type="string", enum={"Epargne", "Courant", "Cheque"}, example="Epargne"),
     *             @OA\Property(property="solde", type="number", format="float", example=2500.75),
     *             @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "suspendu"}, example="actif"),
     *             @OA\Property(property="motifBlocage", type="string", example="Suspicion de fraude"),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de validation invalides",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $compte = Compte::findOrFail($id);

        $validated = $request->validate([
            'titulaire' => 'sometimes|required|string|max:255',
            'type_compte' => 'sometimes|required|in:Epargne,Courant,Cheque',
            'solde' => 'sometimes|required|numeric|min:0',
            'devise' => 'nullable|string|max:3',
            'statut' => 'sometimes|required|in:actif,inactif,bloque,suspendu',
            'motifBlocage' => 'nullable|string|max:500',
            'metadata' => 'nullable|array'
        ]);

        $compte->update($validated);

        return $this->successResponse(
            new CompteResource($compte),
            'Compte mis à jour avec succès'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/archived",
     *     summary="Lister les comptes archivés",
     *     description="Récupère la liste des comptes supprimés logiquement (soft deleted)",
     *     operationId="getArchivedComptes",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page (défaut: 1)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (défaut: 10, max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes archivés récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Liste des comptes archivés récupérée avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Compte")),
     *             @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Paramètres de validation invalides",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function archived(Request $request)
    {
        // Validation des paramètres
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100'
        ]);

        // Récupération des comptes archivés avec pagination
        $perPage = $request->get('limit', 10);
        $comptes = Compte::onlyTrashed()->paginate($perPage);

        // Formatage de la réponse
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
     *     path="/api/v1/comptes/{id}",
     *     summary="Supprimer un compte (soft delete)",
     *     description="Supprime logiquement un compte (soft delete)",
     *     operationId="deleteCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $compte = Compte::findOrFail($id);
        $compte->delete(); // Soft delete

        return $this->successResponse(
            null,
            'Compte supprimé avec succès'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{compte}/transactions",
     *     summary="Lister les transactions d'un compte",
     *     description="Récupère la liste des transactions associées à un compte spécifique",
     *     operationId="getCompteTransactions",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transactions du compte récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transactions du compte récupérées avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Transaction"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function transactions(string $compteId)
    {
        $compte = Compte::findOrFail($compteId);

        // Pour l'instant, retourner un message placeholder
        // À implémenter quand le modèle Transaction sera disponible
        return $this->successResponse(
            [],
            'Transactions du compte récupérées avec succès'
        );
    }
}
