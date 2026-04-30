<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a standardized success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function sendResponse($data, string $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Send a standardized error response.
     *
     * @param string $message
     * @param int $code
     * @param mixed $data
     * @return JsonResponse
     */
    protected function sendError(string $message, int $code = 404, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
