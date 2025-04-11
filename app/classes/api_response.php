<?php

/**
 * API Response Handler
 * Provides a consistent way to send JSON responses from controllers
 */
class ApiResponse {
    /**
     * Send a success response
     * @param mixed $data Optional data to include in response
     * @param string $message Optional success message
     * @param int $status HTTP status code
     */
    public static function success($data = null, $message = '', $status = 200) {
        self::send([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $status);
    }

    /**
     * Send an error response
     * @param string $message Error message
     * @param mixed $errors Optional error details
     * @param int $status HTTP status code
     */
    public static function error($message, $errors = null, $status = 400) {
        self::send([
            'success' => false,
            'error' => $message,
            'errors' => $errors
        ], $status);
    }

    /**
     * Send the actual JSON response
     * @param array $data Response data
     * @param int $status HTTP status code
     */
    private static function send($data, $status) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
