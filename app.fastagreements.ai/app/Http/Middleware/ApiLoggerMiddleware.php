<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Format request data
        $requestData = $request->all();
        $formattedRequest = $this->formatData($requestData);

        Log::info('Global API Request: ' . $request->method() . ' ' . $request->fullUrl(), [
            'payload' => $formattedRequest,
            'ip' => $request->ip()
        ]);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        // Format response data
        $responseData = $response->getContent();
        // If it's JSON, decode it for better readability
        $decodedResponse = json_decode($responseData, true);
        
        $isSuccess = $response->isSuccessful();
        
        // Sometimes APIs return HTTP 200 but explicitly indicate failure in the JSON payload
        if ($decodedResponse !== null && isset($decodedResponse['status'])) {
            if (in_array($decodedResponse['status'], [false, 0, '0', 'false', 'error'], true)) {
                $isSuccess = false;
            }
        }

        if ($isSuccess) {
            $finalResponse = 'API SUCCESS';
        } else {
            $finalResponse = $decodedResponse !== null ? $decodedResponse : (strlen($responseData) > 5000 ? substr($responseData, 0, 5000) . '... [TRUNCATED]' : $responseData);
        }

        Log::info('Global API Response: ' . $request->method() . ' ' . $request->fullUrl(), [
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'payload' => $finalResponse,
        ]);

        return $response;
    }

    /**
     * Recursively format data to prevent logging massive base64 strings
     * and correctly display uploaded files.
     */
    private function formatData(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof UploadedFile) {
                $data[$key] = [
                    '[FILE_UPLOAD]' => true,
                    'original_name' => $value->getClientOriginalName(),
                    'mime_type' => $value->getMimeType(),
                    'size_bytes' => $value->getSize(),
                ];
            } elseif (is_array($value)) {
                $data[$key] = $this->formatData($value);
            } elseif (is_string($value)) {
                // Truncate massive base64 strings or very long strings (like signatures)
                if (strlen($value) > 2000) {
                    $data[$key] = substr($value, 0, 100) . '... [TRUNCATED - ' . strlen($value) . ' bytes]';
                }
            }
        }
        return $data;
    }
}
