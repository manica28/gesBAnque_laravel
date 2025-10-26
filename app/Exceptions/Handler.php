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
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            // Log pour déboguer
            Log::info('MethodNotAllowedHttpException caught', [
                'method' => $request->method(),
                'path' => $request->path(),
                'is_api' => $request->is('api/*'),
                'expects_json' => $request->expectsJson(),
                'is_json' => $request->isJson(),
                'accept' => $request->header('Accept'),
                'content_type' => $request->header('Content-Type')
            ]);

            // Vérifier si c'est une requête API, JSON-expecting, ou avec contenu JSON
            if ($request->is('api/*') || $request->expectsJson() || $request->isJson()) {
                return $this->errorResponse(
                    'La méthode ' . $request->method() . ' n\'est pas supportée pour cette route.',
                    405,
                    [
                        'method' => $request->method(),
                        'route' => $request->path(),
                        'supported_methods' => $e->getHeaders()['Allow'] ?? []
                    ]
                );
            }
        });
    }
}
