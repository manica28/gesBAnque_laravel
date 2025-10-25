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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

 
    public function update(Request $request, string $id)
    {
        //
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
