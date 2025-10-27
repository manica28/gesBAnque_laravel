<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlockCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Les admins peuvent bloquer les comptes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motif' => 'required|string|max:255',
            'duree' => 'required|integer|min:1',
            'unite' => 'required|string|in:jour,jours,semaine,semaines,mois,annee,annees',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'motif.required' => 'Le motif de blocage est obligatoire.',
            'motif.string' => 'Le motif de blocage doit être une chaîne de caractères.',
            'motif.max' => 'Le motif de blocage ne peut pas dépasser 255 caractères.',
            'duree.required' => 'La durée de blocage est obligatoire.',
            'duree.integer' => 'La durée doit être un nombre entier.',
            'duree.min' => 'La durée doit être d\'au moins 1.',
            'unite.required' => 'L\'unité de temps est obligatoire.',
            'unite.in' => 'L\'unité doit être : jour, jours, semaine, semaines, mois, annee ou annees.',
        ];
    }
}
