<?php
/**
 * Theme Asset handler
 *
 * Serves theme assets (images, CSS, JS, etc.) securely by checking if the requested
 * theme and asset path are valid and accessible.
 *
 * This is a standalone handler that doesn't require the full application initialization.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path if not defined
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Basic security checks
if (!isset($_GET['theme']) || !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['theme'])) {
    http_response_code(400);
    exit('Invalid theme specified');
}

if (!isset($_GET['path']) || empty($_GET['path'])) {
    http_response_code(400);
    exit('No asset path specified');
}

$themeId = $_GET['theme'];
$assetPath = $_GET['path'];

// Validate asset path (only alphanumeric, hyphen, underscore, dot, and forward slash)
if (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $assetPath)) {
    http_response_code(400);
    exit('Invalid asset path');
}

// Prevent directory traversal
if (strpos($assetPath, '..') !== false) {
    http_response_code(400);
    exit('Invalid asset path');
}

// Build full path to the asset
$themesDir = dirname(dirname(__DIR__)) . '/themes';
$fullPath = realpath("$themesDir/$themeId/$assetPath");

// Additional security check to ensure the path is within the themes directory
if ($fullPath === false) {
    http_response_code(404);
    header('Content-Type: text/plain');
    error_log("Asset not found: $themesDir/$themeId/$assetPath");
    exit("Asset not found: $themesDir/$themeId/$assetPath");
}

if (strpos($fullPath, realpath($themesDir)) !== 0) {
    http_response_code(400);
    header('Content-Type: text/plain');
    error_log("Security violation: Attempted to access path outside themes directory: $fullPath");
    exit('Invalid asset path');
}

// Check if the file exists and is readable
if (!file_exists($fullPath) || !is_readable($fullPath)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    error_log("File not found or not readable: $fullPath");
    exit("File not found or not readable: " . basename($fullPath));
}

// Clear any previous output
if (ob_get_level()) {
    ob_clean();
}

// Determine content type based on file extension
$extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
$contentTypes = [
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'json' => 'application/json',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'webp' => 'image/webp',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'  => 'font/ttf',
    'eot'  => 'application/vnd.ms-fontobject',
];

$contentType = $contentTypes[$extension] ?? 'application/octet-stream';

// Set proper headers
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($fullPath));

// Cache for 24 hours (86400 seconds)
$expires = 86400;
header('Cache-Control: public, max-age=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
header('Pragma: cache');

// Output the file
readfile($fullPath);
