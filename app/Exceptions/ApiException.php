<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;

class ApiException extends Exception
{
    use ApiResponseTrait;

    protected $statusCode;
    protected $errors;

    public function __construct(
        string $message = 'Une erreur est survenue',
        int $statusCode = 400,
        array $errors = [],
        \Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Convertir l'exception en réponse JSON
     */
    public function render(): JsonResponse
    {
        return $this->errorResponse(
            $this->getMessage(),
            $this->statusCode,
            $this->errors
        );
    }

    /**
     * Créer une exception pour ressource non trouvée
     */
    public static function notFound(string $resource = 'Ressource'): self
    {
        return new self("$resource non trouvée", 404);
    }

    /**
     * Créer une exception pour accès refusé
     */
    public static function forbidden(string $message = 'Accès refusé'): self
    {
        return new self($message, 403);
    }

    /**
     * Créer une exception pour données invalides
     */
    public static function validationError(array $errors): self
    {
        return new self('Données de validation invalides', 422, $errors);
    }

    /**
     * Créer une exception pour limite de taux dépassée
     */
    public static function rateLimitExceeded(int $retryAfter = 60): self
    {
        return new self('Limite de requêtes dépassée', 429, ['retry_after' => $retryAfter]);
    }

    /**
     * Créer une exception pour service indisponible
     */
    public static function serviceUnavailable(string $message = 'Service temporairement indisponible'): self
    {
        return new self($message, 503);
    }
}
