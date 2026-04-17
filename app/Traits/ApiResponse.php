<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Unified success response.
     *
     * JSON shape:
     * {
     *   "success": true,
     *   "message": "Done",
     *   "data":    {},
     *   "meta":    {}   ← pagination, filters, etc.
     * }
     */
    protected function successResponse(
        mixed  $data    = null,
        string $message = 'Done',
        int    $code    = 200,
        array  $meta    = []
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (! empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $code);
    }

    /**
     * Unified error response.
     */
    protected function errorResponse(
        string $message,
        int    $code   = 400,
        mixed  $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Paginated success response — extracts pagination meta automatically.
     */
    protected function paginatedResponse(
        mixed  $paginator,
        string $message = 'Done',
        int    $code    = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], $code);
    }

    // ── Backwards-compat aliases ──────────────────────
    protected function success($data = null, string $message = 'Done', int $code = 200): JsonResponse
    {
        return $this->successResponse($data, $message, $code);
    }

    protected function error(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return $this->errorResponse($message, $code, $errors);
    }
}
