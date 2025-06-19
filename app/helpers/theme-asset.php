<?php
/**
 * Theme Asset Handler
 *
 * Serves theme assets (images, CSS, JS, etc.) securely by checking if the requested
 * theme and asset path are valid and accessible.
 */

// Include necessary files
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../core/ConfigLoader.php';

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
$fullPath = __DIR__ . "/../../themes/$themeId/$assetPath";

// Check if the file exists and is readable
if (!file_exists($fullPath) || !is_readable($fullPath)) {
    http_response_code(404);
    exit('Asset not found');
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
