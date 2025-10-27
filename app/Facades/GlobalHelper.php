<?php

namespace App\Facades;

use Illuminate\Http\JsonResponse;

class GlobalHelper
{
    public static function responseSuccess($message, $data = null, $code = 200): JsonResponse
    {
        return response()->json([
            'meta'  => [
                'message'   => $message,
                'code'      => $code,
            ],
            'data'      => $data
        ], $code);
    }

    public static function responseError($message, $data = [], $code = 400): JsonResponse
    {
        if ($code === 401) {
            return response()->json([
                'message'   => 'Unauthenticated'
            ], $code);
        }
        $meta = [
            'message'   => $message instanceof \Exception ? $message->getMessage() : $message,
            'code'      => $code,
            'number'    => $message instanceof \Exception ? $message->getLine() : "",
            'action'    => $message instanceof \Exception ? $message->getFile() : ""
        ];

        return response()->json([
            'meta'   => $meta,
            'data'  => $data
        ], $code);
    }
}
