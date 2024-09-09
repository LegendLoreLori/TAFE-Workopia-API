<?php

namespace App\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    /**
     * Helper function to manually rollback transactions made during tests
     *
     * @param $e
     * @param string $message
     * @return void
     * @throws HttpResponseException
     */
    public static function rollback($e, string $message = "Something went wrong! Process not completed"): void
    {

        DB::rollBack();
        self::throw($e, $message);
    }

    /**
     *  Logs an exception at info level and throws a HTTP exception with status 500
     *
     * @param $e
     * @param string $message
     * @throws HttpResponseException
     */
    public static function throw($e, string $message = "Something went wrong! Process not completed")
    {
        Log::info($e);
        throw new HttpResponseException(response()->json(["message" => $message], 500));
    }

    /**
     * Send a success response.
     *
     * @param $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function sendSuccess($data, string $message, int $code = 200): JsonResponse
    {
        $response = [
            'success' => 'true',
            'message' => $message,
            'data' => $data
        ];

        return response()->json($response, $code);
    }
}
