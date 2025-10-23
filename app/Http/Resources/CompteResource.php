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
        ];
    }
}
