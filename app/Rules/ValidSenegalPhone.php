<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSenegalPhone implements ValidationRule
{
    /**
     * Exécuter la règle de validation.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nettoyer le numéro
        $cleanNumber = $this->cleanPhoneNumber($value);

        if (!$this->isValidSenegalPhone($cleanNumber)) {
            $fail('Le numéro de téléphone doit être un numéro sénégalais valide.');
        }
    }

    /**
     * Nettoyer le numéro de téléphone
     */
    private function cleanPhoneNumber(string $phone): string
    {
        // Supprimer les espaces, tirets et parenthèses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Si commence par +221, le garder tel quel
        if (str_starts_with($phone, '+221')) {
            return $phone;
        }

        // Si commence par 221, ajouter +
        if (str_starts_with($phone, '221')) {
            return '+' . $phone;
        }

        // Sinon, ajouter +221 au début
        return '+221' . $phone;
    }

    /**
     * Vérifier si c'est un numéro sénégalais valide
     */
    private function isValidSenegalPhone(string $phone): bool
    {
        // Doit commencer par +221
        if (!str_starts_with($phone, '+221')) {
            return false;
        }

        // Doit faire exactement 13 caractères (+221 + 9 chiffres)
        if (strlen($phone) !== 13) {
            return false;
        }

        // Le premier chiffre après +221 doit être 6, 7 ou 8
        $firstDigit = substr($phone, 4, 1);
        if (!in_array($firstDigit, ['6', '7', '8'])) {
            return false;
        }

        // Les 8 derniers caractères doivent être des chiffres
        if (!is_numeric(substr($phone, 5))) {
            return false;
        }

        return true;
    }
}