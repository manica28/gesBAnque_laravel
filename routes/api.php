<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\CompteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques (si nécessaire)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes API versionnées
Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Routes des Comptes 
    |--------------------------------------------------------------------------
    |
    | Routes pour la gestion des comptes bancaires
    | - Admin peut lister tous les comptes
    | - Client peut lister ses propres comptes
    |
    */

    /*
     * GET /api/v1/comptes
     *
     * Récupère la liste des comptes avec filtrage et pagination
     *
     * Paramètres de requête (optionnels) :
     * - page: numéro de page (défaut: 1)
     * - limit: nombre d'éléments par page (défaut: 10, max: 100)
     * - type: type de compte ('Epargne', 'Courant', 'Cheque')
     * - statut: statut du compte ('actif', 'inactif', 'bloque', 'suspendu')
     * - search: recherche textuelle sur titulaire et numéro de compte
     * - sort: champ de tri ('date_creation', 'solde', 'titulaire', 'numero_compte')
     * - order: ordre de tri ('asc', 'desc')
     * - client_id: filtrer par ID client (pour les admins)
     *
     * Réponse :
     * {
     *   "success": true,
     *   "message": "Liste des comptes récupérée avec succès",
     *   "data": [...],
     *   "pagination": {...}
     * }
     *
     * Codes d'erreur :
     * - 422: Paramètres de validation invalides
     * - 500: Erreur interne du serveur
     */
    Route::get('/comptes', [CompteController::class, 'index']);

    /*
     * GET /api/v1/comptes/archived
     *
     * Récupère la liste des comptes archivés (soft deleted) avec pagination
     *
     * Paramètres de requête (optionnels) :
     * - page: numéro de page (défaut: 1)
     * - limit: nombre d'éléments par page (défaut: 10, max: 100)
     *
     * Note: Les comptes archivés sont ceux qui ont été supprimés logiquement
     * mais conservés en base pour l'historique
     *
     * Réponse :
     * {
     *   "success": true,
     *   "message": "Liste des comptes archivés récupérée avec succès",
     *   "data": [...],
     *   "pagination": {...}
     * }
     */
    Route::get('/comptes/archived', [CompteController::class, 'archived']);

});
