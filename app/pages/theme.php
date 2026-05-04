<?php

use App\Helpers\Theme;

/**
 * Theme Management Controller
 *
 * Handles theme switching and management functionality.
 * Allows users to view available themes and change the active theme.
 *
 * Actions:
 * - switch_to: Changes the active theme for the current user
 */

// Initialize security
require_once '../app/helpers/security.php';
require_once '../app/helpers/url_canonicalizer.php';
$security = SecurityHelper::getInstance();

// Only allow access to logged-in users
if (!Session::isValidSession()) {
    header('Location: ' . $app_root . '?page=login');
    exit;
}

// Get any old feedback messages
include_once '../app/helpers/feedback.php';

$isGetRequest = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'GET';
if ($isGetRequest) {
    $canonicalPolicy = [
        'page' => [
            'type' => 'literal',
            'value' => 'theme',
        ],
        'switch_to' => [
            'type' => 'string',
        ],
        'csrf_token' => [
            'type' => 'string',
            'include_if' => static function (array $sourceQuery): bool {
                return trim((string)($sourceQuery['switch_to'] ?? '')) !== '';
            },
        ],
    ];
    $canonicalQuery = app_url_build_query_from_policy($_GET, $canonicalPolicy);

    // Keep theme page URLs deterministic while preserving switch action inputs.
    app_url_redirect_to_canonical_query((string)$app_root, $_GET, $canonicalQuery);
}

// Handle theme switching
if (isset($_GET['switch_to'])) {
    $themeName = $_GET['switch_to'];

    // Validate CSRF token for state-changing operations
    if (!$security->verifyCsrfToken($_GET['csrf_token'] ?? '')) {
        Feedback::flash('SECURITY', 'CSRF_INVALID');
        header("Location: $app_root?page=theme");
        exit();
    }

    if (Theme::setCurrentTheme($themeName)) {
        // Set success message
        Feedback::flash('THEME', 'THEME_CHANGED');
    } else {
        // Set error message
        Feedback::flash('THEME', 'THEME_CHANGE_FAILED');
    }

    // Redirect back to prevent form resubmission
    $redirect = $app_root . '?page=theme';
    header("Location: $redirect");
    exit;
}

// Get available themes and current theme for the view
$themes = Theme::getAvailableThemes();
$currentTheme = Theme::getCurrentThemeName();

// Prepare theme data with screenshot URLs and metadata for the view
$themeData = [];
foreach ($themes as $id => $name) {
    $meta = Theme::getThemeMetadata($id);
    $themeData[$id] = [
        'name' => $meta['name'] ?? $name,
        'description' => $meta['description'] ?? '',
        'version' => $meta['version'] ?? '',
        'author' => $meta['author'] ?? '',
        'tags' => $meta['tags'] ?? [],
        'type' => $meta['type'] ?? '',
        'path' => $meta['path'] ?? '',
        'last_modified' => $meta['last_modified'] ?? null,
        'file_count' => $meta['file_count'] ?? null,
        'screenshotUrl' => Theme::getAssetUrl($id, 'screenshot.png'),
        'isActive' => $id === $currentTheme
    ];
}

// Make theme data available to the view
$themes = $themeData;

// Generate CSRF token for the form
$csrf_token = $security->generateCsrfToken();

// Load the template
include '../app/templates/theme.php';
