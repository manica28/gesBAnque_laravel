<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:cheque,epargne,courant',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string|size:3',
            'solde' => 'nullable|numeric|min:0',
            'client' => 'required|array',
            'client.titulaire' => 'required|string|max:255',
'client.nci' => ['nullable', 'string', new \App\Rules\ValidSenegalNCI()],
'client.email' => 'required|email|unique:users,email',
'client.telephone' => ['required', 'string', new \App\Rules\ValidSenegalPhone(), 'unique:users,telephone'],
            'client.adresse' => 'required|string|max:500',

        
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être cheque, epargne ou courant.',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.size' => 'La devise doit contenir exactement 3 caractères.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.titulaire.required' => 'Le nom du titulaire est obligatoire.',
            'client.email.required' => 'L\'email est obligatoire.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.adresse.required' => 'L\'adresse est obligatoire.',
            
        ];
    }

    
}
