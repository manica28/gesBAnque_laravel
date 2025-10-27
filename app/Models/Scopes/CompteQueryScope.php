<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompteQueryScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Par défaut, on exclut les comptes supprimés (soft deletes)
        $builder->whereNull('deleted_at');
    }

    /**
     * Filtrer par numéro de compte
     */
    public function scopeNumero(Builder $query, string $numero): Builder
    {
        return $query->where('numero_compte', 'ILIKE', "%{$numero}%");
    }

    /**
     * Filtrer par client
     */
    public function scopeClient(Builder $query, int $clientId): Builder
    {
        return $query->where('id_client', $clientId);
    }

    /**
     * Filtrer par type de compte
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        // Mapping des types pour correspondre aux valeurs de la base de données
        $typeMapping = [
            'Epargne' => 'Epargne',
            'Courant' => 'Courant',
            'Cheque' => 'Cheque',
            'epargne' => 'Epargne',
            'courant' => 'Courant',
            'cheque' => 'Cheque'
        ];

        $mappedType = $typeMapping[$type] ?? $type;
        return $query->where('type_compte', $mappedType);
    }

    /**
     * Filtrer par statut
     */
    public function scopeStatut(Builder $query, string $statut): Builder
    {
        return $query->where('statut', $statut);
    }

    /**
     * Recherche textuelle sur titulaire et numéro
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('titulaire', 'ILIKE', "%{$search}%")
              ->orWhere('numero_compte', 'ILIKE', "%{$search}%");
        });
    }

    /**
     * Trier les résultats
     */
    public function scopeOrderByField(Builder $query, string $field, string $direction = 'asc'): Builder
    {
        $allowedFields = ['date_creation', 'solde', 'titulaire', 'numero_compte'];

        if (in_array($field, $allowedFields)) {
            return $query->orderBy($field, $direction);
        }

        return $query;
    }

    /**
     * Pagination avec paramètres par défaut
     */
    public function scopePaginateWithDefaults(Builder $query, int $perPage = 10, int $page = 1)
    {
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Règles de validation pour les filtres
     */
    public static function getValidationRules(): array
    {
        return [
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'type' => 'in:Epargne,Courant,Cheque',
            'statut' => 'in:actif,inactif,bloque,suspendu',
            'search' => 'string|nullable|max:255',
            'sort' => 'in:date_creation,solde,titulaire,numero_compte',
            'order' => 'in:asc,desc',
            'client_id' => 'integer|exists:clients,id_client',
        ];
    }

    /**
     * Appliquer tous les filtres depuis une requête HTTP
     */
    public static function applyFiltersToQuery(Builder $query, array $filters): Builder
    {
        // Filtrage par type
        if (isset($filters['type']) && !empty($filters['type'])) {
            // Mapping des types pour correspondre aux valeurs de la base de données
            $typeMapping = [
                'Epargne' => 'Epargne',
                'Courant' => 'Courant',
                'Cheque' => 'Cheque',
                'epargne' => 'Epargne',
                'courant' => 'Courant',
                'cheque' => 'Cheque'
            ];

            $mappedType = $typeMapping[$filters['type']] ?? $filters['type'];
            $query->where('type_compte', $mappedType);
        }

        // Filtrage par statut
        if (isset($filters['statut']) && !empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        // Recherche textuelle
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('titulaire', 'ILIKE', "%{$search}%")
                  ->orWhere('numero_compte', 'ILIKE', "%{$search}%");
            });
        }

        // Filtrage par client (pour les clients)
        if (isset($filters['client_id']) && !empty($filters['client_id'])) {
            $query->where('id_client', $filters['client_id']);
        }

        // Tri
        $sortField = $filters['sort'] ?? 'date_creation';
        $sortOrder = $filters['order'] ?? 'desc';

        $allowedSortFields = ['date_creation', 'solde', 'titulaire', 'numero_compte'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder);
        }

        return $query;
    }

    /**
     * Méthode d'instance pour compatibilité avec les scopes Laravel
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        return self::applyFiltersToQuery($query, $filters);
    }
}
