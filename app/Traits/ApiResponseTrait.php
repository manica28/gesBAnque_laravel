<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Format de réponse standard pour les succès
     */
    protected function successResponse($data = null, $message = 'Opération réussie', $statusCode = 200, $pagination = null)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($pagination !== null) {
            $response['pagination'] = $pagination;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Format de réponse standard pour les erreurs
     */
    protected function errorResponse($message = 'Une erreur est survenue', $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Format de pagination standard
     */
    protected function formatPagination($paginatedData)
    {
        return [
            'currentPage' => $paginatedData->currentPage(),
            'totalPages' => $paginatedData->lastPage(),
            'totalItems' => $paginatedData->total(),
            'itemsPerPage' => $paginatedData->perPage(),
            'hasNext' => $paginatedData->hasMorePages(),
            'hasPrevious' => $paginatedData->currentPage() > 1,
        ];
    }

    /**
     * Format des liens de pagination
     */
    protected function formatPaginationLinks($paginatedData, $baseUrl, $queryParams = [])
    {
        $links = [
            'self' => $this->buildUrl($baseUrl, array_merge($queryParams, ['page' => $paginatedData->currentPage()])),
            'first' => $this->buildUrl($baseUrl, array_merge($queryParams, ['page' => 1])),
            'last' => $this->buildUrl($baseUrl, array_merge($queryParams, ['page' => $paginatedData->lastPage()])),
        ];

        if ($paginatedData->hasMorePages()) {
            $links['next'] = $this->buildUrl($baseUrl, array_merge($queryParams, ['page' => $paginatedData->currentPage() + 1]));
        }

        if ($paginatedData->currentPage() > 1) {
            $links['previous'] = $this->buildUrl($baseUrl, array_merge($queryParams, ['page' => $paginatedData->currentPage() - 1]));
        }

        return $links;
    }

    /**
     * Construction d'URL avec paramètres
     */
    private function buildUrl($baseUrl, $params = [])
    {
        $queryString = http_build_query($params);
        return $baseUrl . ($queryString ? '?' . $queryString : '');
    }
}