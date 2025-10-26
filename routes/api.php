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

    // Routes des comptes avec noms appropriés pour HATEOAS
    Route::apiResource('comptes', CompteController::class)->names([
        'index' => 'api.v1.comptes.index',
        'store' => 'api.v1.comptes.store', //post
        'show' => 'api.v1.comptes.show',
        'update' => 'api.v1.comptes.update',
        'destroy' => 'api.v1.comptes.destroy'
    ]);

    // Route spécifique pour les comptes archivés
    Route::get('/comptes/archived', [CompteController::class, 'archived'])->name('api.v1.comptes.archived');

    // Route pour les transactions d'un compte (pour HATEOAS)
    Route::get('/comptes/{compte}/transactions', [CompteController::class, 'transactions'])->name('api.v1.comptes.transactions');

    // Route pour mettre à jour les informations client d'un compte
    Route::patch('/comptes/{compte}/client', [CompteController::class, 'updateClientInfo'])->name('api.v1.comptes.updateClientInfo');

    // Routes pour les clients (nécessaires pour HATEOAS)
    Route::get('/clients/{client}', function ($client) {
        // Placeholder pour la route client - à implémenter plus tard
        return response()->json(['message' => 'Client route placeholder']);
    })->name('api.v1.clients.show');

});
