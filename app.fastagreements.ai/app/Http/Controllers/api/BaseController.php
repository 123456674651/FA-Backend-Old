<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Success response method
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function sendResponse($data = null, string $message = 'Success', int $code = 200, ?int $responseCode = null): JsonResponse
    {
        // Maintain order: status, message, responseCode, data
        $response = [
            'status' => true,
            'message' => $message,
        ];

        if (!is_null($responseCode)) {
            $response['responseCode'] = $responseCode;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Error response method
     *
     * @param string $message
     * @param mixed $errors
     * @param int $code
     * @return JsonResponse
     */
    public function sendError(string $message = 'Error', $errors = null, int $code = 200, ?int $responseCode = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if (!is_null($responseCode)) {
            $response['responseCode'] = $responseCode;
        }

        // Force an empty object using (object) [] or new \stdClass()
        $response['data'] = (object) [];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Validation error response method
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    public function sendValidationError($errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->sendError($message, $errors, 422);
    }

    /**
     * Not found error response method
     *
     * @param string $message
     * @return JsonResponse
     */
    public function sendNotFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->sendError($message, null, 200);
    }

    /**
     * Unauthorized error response method
     *
     * @param string $message
     * @return JsonResponse
     */
    public function sendUnauthorized(string $message = 'Unauthorized', ?int $responseCode = null): JsonResponse
    {
        return $this->sendError($message, null, 401, $responseCode);
    }

    /**
     * Server error response method
     *
     * @param string $message
     * @param mixed $error
     * @return JsonResponse
     */
    public function sendServerError(string $message = 'Internal server error', $error = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        // Only show error details in debug mode
        if ($error !== null && config('app.debug')) {
            $response['error'] = $error;
        }

        return response()->json($response, 500);
    }

    /**
     * Created response method
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public function sendCreated($data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->sendResponse($data, $message, 201);
    }

    public function sendApplicationMaintanceResponse($updateStatus, $appMode, $code, $msg, $badge, $ads, $maintenance)
    {
        return response()->json([
            'status' => true,
            'message' => $msg,
            'responseCode' => $code,
            'data' => [
                // Logic updated here:
                'is_application_update' => ($maintenance == 'is_partial_updated') ? 'is_updated' : $updateStatus,

                'is_application_version_mode' => $appMode,
                'badge_count' => $badge,
                'ads_active' => $ads,
                'maintenance_mode' => (boolean) $maintenance
            ]
        ]);
    }

    public function generateUniqueId($length = 24)
    {
        $psw = '';
        $string = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($p = 0; $p <= $length; $p++) {
            $psw .= substr($string, rand(0, strlen($string) - 1), 1);
        }
        return $psw;
    }

    public function getSecureKey()
    {
        $string = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $stamp = time();
        $secure_key = $pre = $post = '';
        for ($p = 0; $p <= 10; $p++) {
            $pre .= substr($string, rand(0, strlen($string) - 1), 1);
        }

        for ($i = 0; $i < strlen($stamp); $i++) {
            $key = substr($string, substr($stamp, $i, 1), 1);
            $secure_key .= (rand(0, 1) == 0 ? $key : (rand(0, 1) == 1 ? strtoupper($key) : rand(0, 9)));
        }

        for ($p = 0; $p <= 10; $p++) {
            $post .= substr($string, rand(0, strlen($string) - 1), 1);
        }
        return $pre . '-' . $secure_key . $post;
    }
}
