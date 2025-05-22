<?php
/**
 * Theme Management Controller
 *
 * Handles theme switching and management functionality.
 * Allows users to view available themes and change the active theme.
 *
 * Actions:
 * - switch_to: Changes the active theme for the current user
 */

// Only allow access to logged-in users
if (!Session::isValidSession()) {
    header('Location: ' . $app_root . '?page=login');
    exit;
}

// Handle theme switching
if (isset($_GET['switch_to'])) {
    $themeName = $_GET['switch_to'];

    // Validate CSRF token for state-changing operations
    require_once '../app/helpers/security.php';
    $security = SecurityHelper::getInstance();

    if (!$security->verifyCsrfToken($_GET['csrf_token'] ?? '')) {
        Feedback::flash('SECURITY', 'CSRF_INVALID');
        header("Location: $app_root?page=theme");
        exit();
    }

    if (\App\Helpers\Theme::setCurrentTheme($themeName)) {
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
$themes = \App\Helpers\Theme::getAvailableThemes();
$currentTheme = \App\Helpers\Theme::getCurrentThemeName();

// Generate CSRF token for the form
$csrf_token = $security->generateCsrfToken();

// Get any new feedback messages
include '../app/helpers/feedback.php';

// Load the template
include '../app/templates/theme.php';
