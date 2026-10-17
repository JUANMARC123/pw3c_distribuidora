<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function jsonResponse($data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = '', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function paginatedResponse($paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    protected function safePaginate(Builder $query, Request $request, int $defaultPerPage = 15): JsonResponse
    {
        $perPage = $request->per_page ?? $defaultPerPage;

        if ($perPage == -1 || $perPage === '-1') {
            return $this->jsonResponse($query->get());
        }

        return $this->paginatedResponse(
            $query->paginate((int) $perPage)
        );
    }
}
