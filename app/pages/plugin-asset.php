<?php
/**
 * Plugin Asset handler
 *
 * Serves plugin assets (CSS, JS, images, fonts, etc.) securely by validating the
 * requested plugin name, asset path and enabled status. This way each plugin can
 * keep its assets in its local folders and only load them when needed.
 */

require_once __DIR__ . '/../classes/session.php';

$pluginName = $_GET['plugin'] ?? '';
$assetPath = $_GET['path'] ?? '';

$sendError = static function(int $code, string $message): void {
    http_response_code($code);
    header('Content-Type: text/plain');
    exit($message);
};

if ($pluginName === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $pluginName)) {
    $sendError(400, 'Invalid plugin specified');
}

if ($assetPath === '') {
    $sendError(400, 'No asset path specified');
}

if (!preg_match('/^[a-zA-Z0-9_\-.\/]+$/', $assetPath) || strpos($assetPath, '..') !== false) {
    $sendError(400, 'Invalid asset path');
}

if (!isset($GLOBALS['enabled_plugins'][$pluginName])) {
    $sendError(404, 'Plugin not enabled');
}

$pluginsRoot = realpath(dirname(__DIR__, 2) . '/plugins');
if ($pluginsRoot === false) {
    $sendError(500, 'Plugins directory missing');
}

$pluginBase = realpath($pluginsRoot . '/' . $pluginName);
if ($pluginBase === false || strpos($pluginBase, $pluginsRoot) !== 0) {
    $sendError(404, 'Plugin not found');
}

$fullPath = realpath($pluginBase . '/' . ltrim($assetPath, '/'));
if ($fullPath === false || strpos($fullPath, $pluginBase) !== 0 || !is_file($fullPath) || !is_readable($fullPath)) {
    $sendError(404, 'Asset not found');
}

$extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
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
    'woff2'=> 'font/woff2',
    'ttf'  => 'font/ttf',
    'eot'  => 'application/vnd.ms-fontobject'
];

header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=86400');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

readfile($fullPath);
