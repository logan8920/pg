<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function errorResponse(string $message, int $code = 0, int $httpStatus = 401)
    {
        return response()->json([
            'status' => false,
            'response_code' => $code,
            'message' => $message
        ], $httpStatus);
    }

    public function successResponse(array $data = [], string $message = 'Success', int $code = 1, int $httpStatus = 200)
    {
        return response()->json([
            'status' => true,
            'response_code' => $code,
            'data' => $data,
            'message' => $message
        ], $httpStatus);
    }
}
