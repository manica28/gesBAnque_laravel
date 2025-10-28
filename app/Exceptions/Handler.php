<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Pour les requêtes API, retourner toujours du JSON avec l'erreur formatée
        if ($request->is('api/*') || $request->expectsJson() || $request->isJson()) {
            $statusCode = $this->getStatusCode($exception);
            $message = $this->getExceptionMessage($exception);

            // En production, ne pas exposer les détails techniques
            $debugInfo = [];
            if (config('app.debug')) {
                $debugInfo = [
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ];
            }

            return $this->errorResponse(
                config('app.debug') ? $this->formatErrorAsCode($message) : $message,
                $statusCode,
                $debugInfo
            );
        }

        return parent::render($request, $exception);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Get the appropriate status code for the exception.
     */
    private function getStatusCode(Throwable $exception): int
    {
        // Pour les exceptions HTTP de Symfony qui ont getStatusCode
        if (method_exists($exception, 'getStatusCode')) {
            /** @var \Symfony\Component\HttpKernel\Exception\HttpException $exception */
            return $exception->getStatusCode();
        }

        // Cas spécifiques
        if ($exception instanceof MethodNotAllowedHttpException) {
            return 405;
        }

        // Pour les exceptions Laravel qui ont getCode()
        $code = $exception->getCode();
        if (is_int($code) && $code >= 400 && $code < 600) {
            return $code;
        }

        // Par défaut, code d'erreur interne du serveur
        return 500;
    }

    /**
     * Get the exception message.
     */
    private function getExceptionMessage(Throwable $exception): string
    {
        return $exception->getMessage() ?: 'Une erreur inattendue est survenue';
    }
}
