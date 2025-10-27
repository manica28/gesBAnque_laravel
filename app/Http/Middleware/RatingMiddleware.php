<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RatingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Enregistrer les informations de rating pour l'utilisateur
            $this->logUserActivity($user, $request);

            // Vérifier si l'utilisateur a dépassé la limite de requêtes
            if ($this->hasExceededRateLimit($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limite de requêtes dépassée. Veuillez réessayer plus tard.',
                    'retry_after' => 60 // secondes
                ], 429);
            }
        }

        return $next($request);
    }

    /**
     * Enregistrer l'activité de l'utilisateur
     */
    private function logUserActivity($user, Request $request)
    {
        // Ici, vous pourriez enregistrer dans une table dédiée ou utiliser Redis/cache
        // Pour cet exemple, on utilise le cache Laravel
        $key = "user_activity:{$user->id_user}";
        $activities = cache()->get($key, []);

        $activities[] = [
            'timestamp' => now()->toISOString(),
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip()
        ];

        // Garder seulement les 100 dernières activités
        if (count($activities) > 100) {
            array_shift($activities);
        }

        cache()->put($key, $activities, now()->addHours(24));
    }

    /**
     * Vérifier si l'utilisateur a dépassé la limite de requêtes
     */
    private function hasExceededRateLimit($user)
    {
        $key = "user_requests:{$user->id_user}";
        $requests = cache()->get($key, []);

        // Nettoyer les requêtes anciennes (plus de 1 minute)
        $requests = array_filter($requests, function ($timestamp) {
            return now()->diffInSeconds($timestamp) < 60;
        });

        // Ajouter la nouvelle requête
        $requests[] = now();

        // Limite : 60 requêtes par minute
        if (count($requests) > 60) {
            return true;
        }

        cache()->put($key, $requests, now()->addMinutes(1));
        return false;
    }
}
