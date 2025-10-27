<?php

namespace App\Exceptions;

use Exception;

class CompteNotFoundException extends Exception
{
    protected $compteId;
    protected $details;

    public function __construct(string $compteId, array $details = [])
    {
        $this->compteId = $compteId;
        $this->details = $details;

        $message = "Le compte avec l'ID {$compteId} n'existe pas";

        parent::__construct($message, 404);
    }

    public function getCompteId(): string
    {
        return $this->compteId;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'COMPTE_NOT_FOUND',
                'message' => $this->getMessage(),
                'details' => array_merge([
                    'compteId' => $this->compteId
                ], $this->details)
            ]
        ], 404);
    }
}