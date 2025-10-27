<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Gérer une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Extraire le nom de l'opération depuis l'URL
        $operationName = $this->extractOperationName($request);

        // Log de la requête entrante avec plus de détails
        Log::info('Opération de création de compte - Requête reçue', [
            'date_heure' => now()->toISOString(),
            'host' => $request->getHost(),
            'nom_operation' => $operationName,
            'ressource' => $request->path(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => [
                'accept' => $request->header('Accept'),
                'content_type' => $request->header('Content-Type'),
                'authorization' => $request->header('Authorization') ? 'Bearer ***' : null,
            ],
        ]);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // en millisecondes

        // Log de la réponse avec le statut
        Log::info('Opération de création de compte - Réponse envoyée', [
            'date_heure' => now()->toISOString(),
            'host' => $request->getHost(),
            'nom_operation' => $operationName,
            'ressource' => $request->path(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'response_size' => strlen($response->getContent()),
        ]);

        return $response;
    }

    /**
     * Extraire le nom de l'opération depuis la requête.
     */
    private function extractOperationName(Request $request): string
    {
        $path = $request->path();
        $method = $request->method();

        // Déterminer le nom de l'opération basé sur la route
        if (str_contains($path, 'comptes') && $method === 'POST') {
            return 'creation_compte';
        } elseif (str_contains($path, 'comptes') && str_contains($path, 'bloquer')) {
            return 'blocage_compte';
        } elseif (str_contains($path, 'comptes') && str_contains($path, 'debloquer')) {
            return 'deblocage_compte';
        } elseif (str_contains($path, 'comptes') && $method === 'GET') {
            return 'consultation_compte';
        } elseif (str_contains($path, 'comptes') && $method === 'PUT') {
            return 'modification_compte';
        } elseif (str_contains($path, 'comptes') && $method === 'DELETE') {
            return 'suppression_compte';
        }

        return 'operation_api_generique';
    }
}