<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnblockCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Les admins peuvent débloquer les comptes
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
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'motif.required' => 'Le motif de déblocage est obligatoire.',
            'motif.string' => 'Le motif de déblocage doit être une chaîne de caractères.',
            'motif.max' => 'Le motif de déblocage ne peut pas dépasser 255 caractères.',
        ];
    }
}