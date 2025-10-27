<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return $this->errorResponse(
                'Accès non autorisé. Authentification requise.',
                401
            );
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur a le rôle requis
        if (!$this->hasRole($user, $role)) {
            return $this->errorResponse(
                'Accès refusé. Permissions insuffisantes.',
                403,
                [
                    'required_role' => $role,
                    'user_role' => $user->role ?? 'none'
                ]
            );
        }

        // Ajouter les permissions de l'utilisateur à la requête
        $request->merge([
            'user_permissions' => $this->getUserPermissions($user),
            'user_role' => $user->role
        ]);

        return $next($request);
    }

    /**
     * Vérifier si l'utilisateur a le rôle requis
     */
    private function hasRole($user, string $requiredRole): bool
    {
        $userRole = $user->role ?? 'client';

        // Hiérarchie des rôles
        $roleHierarchy = [
            'client' => 1,
            'admin' => 2,
            'super_admin' => 3
        ];

        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Récupérer les permissions de l'utilisateur
     */
    private function getUserPermissions($user): array
    {
        $role = $user->role ?? 'client';

        $permissions = [
            'client' => [
                'view_own_accounts',
                'view_own_transactions',
                'create_transaction'
            ],
            'admin' => [
                'view_own_accounts',
                'view_own_transactions',
                'create_transaction',
                'view_all_accounts',
                'view_all_transactions',
                'block_accounts',
                'unblock_accounts',
                'manage_clients'
            ],
            'super_admin' => [
                'view_own_accounts',
                'view_own_transactions',
                'create_transaction',
                'view_all_accounts',
                'view_all_transactions',
                'block_accounts',
                'unblock_accounts',
                'manage_clients',
                'manage_admins',
                'system_settings'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}