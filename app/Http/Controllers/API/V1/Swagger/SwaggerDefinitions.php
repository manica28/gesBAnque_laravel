<?php

namespace App\Http\Controllers\API\V1\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="API Banque",
 *     version="1.0.0",
 *     description="Documentation Swagger de l'API Banque"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8001",
 *     description="Serveur local"
 * )
 * @OA\Server(
 *     url="https://gesbanque-laravel.onrender.com",
 *     description="Serveur de production"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", description="ID unique du compte"),
 *     @OA\Property(property="numero_compte", type="string", description="Numéro unique du compte"),
 *     @OA\Property(property="titulaire", type="string", description="Nom du titulaire"),
 *     @OA\Property(property="type", type="string", enum={"Epargne", "Courant", "Cheque"}, description="Type de compte"),
 *     @OA\Property(property="solde", type="number", format="decimal", description="Solde du compte"),
 *     @OA\Property(property="devise", type="string", default="FCFA", description="Devise du compte"),
 *     @OA\Property(property="date_creation", type="string", format="date-time", description="Date de création"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "suspendu"}, description="Statut du compte"),
 *     @OA\Property(property="metadata", type="object", nullable=true, description="Métadonnées supplémentaires")
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     @OA\Property(property="currentPage", type="integer", description="Page actuelle"),
 *     @OA\Property(property="totalPages", type="integer", description="Nombre total de pages"),
 *     @OA\Property(property="totalItems", type="integer", description="Nombre total d'éléments"),
 *     @OA\Property(property="itemsPerPage", type="integer", description="Éléments par page"),
 *     @OA\Property(property="hasNext", type="boolean", description="Page suivante disponible"),
 *     @OA\Property(property="hasPrevious", type="boolean", description="Page précédente disponible"),
 *     @OA\Property(property="links", type="object",
 *         @OA\Property(property="self", type="string", description="Lien vers la page actuelle"),
 *         @OA\Property(property="first", type="string", description="Lien vers la première page"),
 *         @OA\Property(property="last", type="string", description="Lien vers la dernière page"),
 *         @OA\Property(property="next", type="string", nullable=true, description="Lien vers la page suivante"),
 *         @OA\Property(property="previous", type="string", nullable=true, description="Lien vers la page précédente")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", description="Statut de la réponse"),
 *     @OA\Property(property="message", type="string", description="Message de réponse"),
 *     @OA\Property(property="data", type="object", description="Données de réponse"),
 *     @OA\Property(property="pagination", ref="#/components/schemas/Pagination", nullable=true, description="Informations de pagination")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false, description="Statut d'erreur"),
 *     @OA\Property(property="message", type="string", description="Message d'erreur"),
 *     @OA\Property(property="errors", type="object", nullable=true, description="Détails des erreurs de validation")
 * )
 *
 * @OA\Schema(
 *     schema="ClientData",
 *     type="object",
 *     required={"titulaire", "email", "telephone", "adresse"},
 *     @OA\Property(property="id", type="string", nullable=true, description="ID du client existant (optionnel)"),
 *     @OA\Property(property="titulaire", type="string", description="Nom complet du titulaire"),
 *     @OA\Property(property="nci", type="string", nullable=true, description="Numéro de carte d'identité sénégalaise"),
 *     @OA\Property(property="email", type="string", format="email", description="Adresse email unique"),
 *     @OA\Property(property="telephone", type="string", description="Numéro de téléphone sénégalais"),
 *     @OA\Property(property="adresse", type="string", description="Adresse complète")
 * )
 *
 * @OA\Schema(
 *     schema="CreateCompteRequest",
 *     type="object",
 *     required={"type", "soldeInitial", "devise", "client"},
 *     @OA\Property(property="type", type="string", enum={"cheque", "epargne", "courant"}, description="Type de compte"),
 *     @OA\Property(property="soldeInitial", type="number", minimum=10000, description="Solde initial minimum 10 000"),
 *     @OA\Property(property="devise", type="string", default="FCFA", description="Devise du compte"),
 *     @OA\Property(property="client", ref="#/components/schemas/ClientData", description="Informations du client")
 * )
 *
 * @OA\Schema(
 *     schema="CreateCompteResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Compte créé avec succès"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="id", type="string", format="uuid", example="660f9511-f30c-52e5-b827-557766551111"),
 *         @OA\Property(property="numeroCompte", type="string", example="C00123460"),
 *         @OA\Property(property="titulaire", type="string", example="Cheikh Sy"),
 *         @OA\Property(property="type", type="string", example="cheque"),
 *         @OA\Property(property="solde", type="number", example=500000),
 *         @OA\Property(property="devise", type="string", example="FCFA"),
 *         @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
 *         @OA\Property(property="statut", type="string", example="actif"),
 *         @OA\Property(property="metadata", type="object",
 *             @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
 *             @OA\Property(property="version", type="integer", example=1)
 *         )
 *     )
 * )
 */
class SwaggerDefinitions
{
    // Classe vide : elle sert uniquement à contenir les annotations globales
}
