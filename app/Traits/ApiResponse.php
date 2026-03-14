<?php

namespace App\Traits;

trait ApiResponse
{
    // 成功的响应
    protected function success($data = null, $message = "success", $code = 200)
    {
        return response()->json([
            "code" => $code,
            "message" => $message,
            "data" => $data,
        ]);
    }

    // 失败的响应
    protected function error($message = "error", $code = 400, $data = null)
    {
        return response()->json([
            "code" => $code,
            "message" => $message,
            "data" => $data
        ]);
    }
}
