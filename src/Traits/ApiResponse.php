<?php

namespace Hossam\Licht\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function apiResponse($data, $message = null, $success = 1, $code = 200)
    {
        return response()->json(
            [
                'success' => $success,
                'data' => $data,
                'message' => $message,
                'code' => $code,
            ],
            $code
        );
    }
}
