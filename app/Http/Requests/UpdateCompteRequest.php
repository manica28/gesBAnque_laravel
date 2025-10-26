<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompteRequest extends FormRequest
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
            'titulaire' => 'sometimes|required|string|max:255',
            'informationsClient' => 'sometimes|required|array',
            'informationsClient.telephone' => ['sometimes', 'string', new \App\Rules\ValidSenegalPhone(), 'unique:users,telephone'],
            'informationsClient.email' => 'sometimes|email|unique:users,email',
            'informationsClient.password' => 'sometimes|string|min:8',
            'informationsClient.nci' => ['sometimes', 'string', new \App\Rules\ValidSenegalNCI()],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier qu'au moins un champ est fourni
            $hasTitulaire = $this->has('titulaire');
            $hasClientInfo = $this->has('informationsClient') &&
                           collect($this->input('informationsClient', []))->filter()->isNotEmpty();

            if (!$hasTitulaire && !$hasClientInfo) {
                $validator->errors()->add('general', 'Au moins un champ doit être fourni pour la modification.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'titulaire.required' => 'Le nom du titulaire est obligatoire.',
            'titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères.',
            'titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',
            'informationsClient.required' => 'Les informations client sont obligatoires.',
            'informationsClient.array' => 'Les informations client doivent être un tableau.',
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'informationsClient.email.email' => 'L\'email doit être valide.',
            'informationsClient.email.unique' => 'Cet email est déjà utilisé.',
            'informationsClient.password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'general' => 'Au moins un champ doit être fourni pour la modification.',
        ];
    }


}