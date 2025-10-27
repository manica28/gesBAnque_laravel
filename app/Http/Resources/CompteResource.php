<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Utiliser l'URL de base de la requête pour éviter les conflits entre environnements
        $baseUrl = $request->getSchemeAndHttpHost();

        return [
            'id' => $this->id_compte,
            'numeroCompte' => $this->numero_compte,
            'titulaire' => $this->titulaire,
            'type' => $this->type_compte,
            'solde' => $this->solde,
            'devise' => $this->devise,
            'dateCreation' => $this->date_creation?->toISOString(),
            'statut' => $this->statut,
            'motifBlocage' => $this->when($this->statut === 'bloque', $this->motifBlocage),
            'metadata' => $this->metadata,
            '_links' => [
                'self' => [
                    'href' => $baseUrl . route('api.v1.comptes.show', $this->id_compte, false),
                    'method' => 'GET',
                    'rel' => 'self'
                ],
                'update' => [
                    'href' => $baseUrl . route('api.v1.comptes.update', $this->id_compte, false),
                    'method' => 'PUT',
                    'rel' => 'update'
                ],
                'delete' => [
                    'href' => $baseUrl . route('api.v1.comptes.destroy', $this->id_compte, false),
                    'method' => 'DELETE',
                    'rel' => 'delete'
                ],
                'client' => [
                    'href' => $baseUrl . route('api.v1.clients.show', $this->id_client, false),
                    'method' => 'GET',
                    'rel' => 'client'
                ],
                'transactions' => [
                    'href' => $baseUrl . route('api.v1.comptes.transactions', $this->id_compte, false),
                    'method' => 'GET',
                    'rel' => 'transactions'
                ]
            ]
        ];
    }
}
