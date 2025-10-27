<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSenegalNCI implements ValidationRule
{
    /**
     * Exécuter la règle de validation.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->isValidSenegalNCI($value)) {
            $fail('Le numéro NCI doit être un numéro sénégalais valide (13 chiffres commençant par 1 ou 2).');
        }
    }

    /**
     * Vérifier si c'est un numéro NCI sénégalais valide
     */
    private function isValidSenegalNCI(string $nci): bool
    {
        // Nettoyer les espaces
        $nci = trim($nci);

        // Doit faire exactement 13 caractères
        if (strlen($nci) !== 13) {
            return false;
        }

        // Tous les caractères doivent être des chiffres
        if (!ctype_digit($nci)) {
            return false;
        }

        // Doit commencer par 1 ou 2
        $firstDigit = $nci[0];
        if (!in_array($firstDigit, ['1', '2'])) {
            return false;
        }

        return true;
    }
}