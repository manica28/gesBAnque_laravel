<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Client;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Connexion utilisateur",
     *     description="Authentifie un utilisateur (client ou admin) et retourne un access token + refresh token",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"email","password"},
     *         @OA\Property(property="email", type="string", format="email", example="client@example.com"),
     *         @OA\Property(property="password", type="string", example="password123"),
     *         @OA\Property(property="remember", type="boolean", example=true, description="Se souvenir de la connexion")
     *     )),
     *     @OA\Response(response=200, description="Connexion réussie", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Connexion réussie"),
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="client")
     *             ),
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *             @OA\Property(property="refresh_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     )),
     *     @OA\Response(response=401, description="Identifiants invalides"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
                'remember' => 'boolean'
            ]);

            $user = null;
            $guard = null;

            // Essayer d'abord avec les utilisateurs (table users)
            $dbUser = DB::table('users')->where('email', $request->email)->first();
            if ($dbUser && Hash::check($request->password, $dbUser->mot_de_passe)) {
                // Créer une instance de modèle appropriée
                if ($dbUser->type_user === 'admin') {
                    $user = new Admin();
                    $user->fill((array) $dbUser);
                    $user->id = $dbUser->id_user; // Assigner l'ID correct
                    $guard = 'admin';
                } else {
                    $user = new Client();
                    $user->fill((array) $dbUser);
                    $user->id = $dbUser->id_user; // Assigner l'ID correct
                    $guard = 'client';
                }
            }

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Les identifiants fournis sont incorrects.']
                ]);
            }

            // Créer le token avec Passport et les scopes appropriés
            $scopes = $this->getScopesForUser($user, $guard);
            $tokenResult = $user->createToken('API Access', $scopes);
            $token = $tokenResult->token;

            // Ajouter les claims personnalisés
            $token->withClaims([
                'role' => $this->getUserRole($user, $guard),
                'user_id' => $user->id,
                'guard' => $guard
            ]);

            // Définir l'expiration
            $expiration = $request->remember ? now()->addDays(30) : now()->addHours(1);
            $token->expires_at = $expiration;
            $token->save();

            // Créer le refresh token
            $refreshToken = $user->createToken('Refresh Token', ['refresh'])->accessToken;

            // Stocker les tokens dans les cookies
            $accessCookie = Cookie::make(
                'access_token',
                $tokenResult->accessToken,
                $expiration->diffInMinutes(),
                '/',
                null,
                true, // secure
                true  // httpOnly
            );

            $refreshCookie = Cookie::make(
                'refresh_token',
                $refreshToken,
                now()->addDays(30)->diffInMinutes(), // Refresh token dure plus longtemps
                '/',
                null,
                true,
                true
            );

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name ?? $user->nom,
                    'email' => $user->email,
                    'role' => $this->getUserRole($user, $guard)
                ],
                'access_token' => $tokenResult->accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $expiration->diffInSeconds(now()),
                'scopes' => $scopes
            ], 'Connexion réussie')->withCookie($accessCookie)->withCookie($refreshCookie);

        } catch (ValidationException $e) {
            return $this->errorResponse('Identifiants invalides', 401, $e->errors());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la connexion: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Rafraîchir le token d'accès",
     *     description="Utilise le refresh token pour obtenir un nouveau access token",
     *     operationId="refreshToken",
     *     tags={"Authentification"},
     *     @OA\Response(response=200, description="Token rafraîchi avec succès"),
     *     @OA\Response(response=401, description="Refresh token invalide ou expiré")
     * )
     */
    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refresh_token') ?? $request->bearerToken();

            if (!$refreshToken) {
                return $this->errorResponse('Refresh token manquant', 401);
            }

            // Trouver le token dans la base de données
            $token = \Laravel\Passport\Token::findToken($refreshToken);

            if (!$token || $token->revoked || $token->expires_at < now()) {
                return $this->errorResponse('Refresh token invalide ou expiré', 401);
            }

            $user = $token->user;

            // Révoquer l'ancien token
            $token->revoke();

            // Créer un nouveau token
            $newToken = $user->createToken('API Access', $this->getScopesForUser($user, $this->getGuardFromUser($user)));
            $newToken->token->expires_at = now()->addHours(1);
            $newToken->token->save();

            // Créer un nouveau refresh token
            $newRefreshToken = $user->createToken('Refresh Token', ['refresh'])->accessToken;

            return $this->successResponse([
                'access_token' => $newToken->accessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ], 'Token rafraîchi avec succès');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors du rafraîchissement du token: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Déconnexion utilisateur",
     *     description="Invalide le token d'accès actuel",
     *     operationId="logout",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Déconnexion réussie"),
     *     @OA\Response(response=401, description="Token invalide")
     * )
     */
    public function logout(Request $request)
    {
        try {
            $accessToken = $request->bearerToken();

            if ($accessToken) {
                $token = \Laravel\Passport\Token::findToken($accessToken);
                if ($token) {
                    $token->revoke();
                }
            }

            // Supprimer les cookies
            $accessCookie = Cookie::forget('access_token');
            $refreshCookie = Cookie::forget('refresh_token');

            return $this->successResponse(null, 'Déconnexion réussie')
                ->withCookie($accessCookie)
                ->withCookie($refreshCookie);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la déconnexion: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur', 500);
        }
    }

    /**
     * Obtenir les scopes pour un utilisateur selon son rôle
     */
    private function getScopesForUser($user, string $guard): array
    {
        $role = $this->getUserRole($user, $guard);

        $scopes = [
            'client' => [
                'view-own-accounts',
                'view-own-transactions',
                'create-transaction'
            ],
            'admin' => [
                'view-own-accounts',
                'view-own-transactions',
                'create-transaction',
                'view-all-accounts',
                'view-all-transactions',
                'block-accounts',
                'unblock-accounts',
                'manage-clients'
            ],
            'super_admin' => [
                'view-own-accounts',
                'view-own-transactions',
                'create-transaction',
                'view-all-accounts',
                'view-all-transactions',
                'block-accounts',
                'unblock-accounts',
                'manage-clients',
                'manage-admins',
                'system-settings'
            ]
        ];

        return $scopes[$role] ?? [];
    }

    /**
     * Obtenir le rôle d'un utilisateur
     */
    private function getUserRole($user, string $guard): string
    {
        if ($guard === 'admin') {
            return $user->role ?? 'admin';
        }

        return 'client';
    }

    /**
     * Déterminer le guard d'un utilisateur
     */
    private function getGuardFromUser($user): string
    {
        return $user instanceof Admin ? 'admin' : 'client';
    }
}