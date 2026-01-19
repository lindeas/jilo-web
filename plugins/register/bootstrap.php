<?php

/**
 * Register Plugin Bootstrap
 *
 * Initializes the register plugin using the App API pattern.
 */

// Define plugin base path if not already defined
if (!defined('PLUGIN_REGISTER_PATH')) {
    define('PLUGIN_REGISTER_PATH', __DIR__ . '/');
}

require_once PLUGIN_REGISTER_PATH . 'helpers.php';
require_once PLUGIN_REGISTER_PATH . 'controllers/register.php';

// Register route with dispatcher class
register_plugin_route_prefix('register', [
    'dispatcher' => \Plugins\Register\Controllers\RegisterController::class,
    'access' => 'public',
    'defaults' => ['action' => 'register'],
    'plugin' => 'register',
]);

// Add to publicly accessible pages
register_hook('filter_public_pages', function($pages) {
    $pages[] = 'register';
    return $pages;
});

// Configuration for main menu injection
define('REGISTRATIONS_MAIN_MENU_SECTION', 'main');
define('REGISTRATIONS_MAIN_MENU_POSITION', 30);
register_hook('main_public_menu', function($ctx) {
    $section = defined('REGISTRATIONS_MAIN_MENU_SECTION') ? REGISTRATIONS_MAIN_MENU_SECTION : 'main';
    $position = defined('REGISTRATIONS_MAIN_MENU_POSITION') ? REGISTRATIONS_MAIN_MENU_POSITION : 100;
    $appRoot = isset($ctx['app_root']) ? htmlspecialchars($ctx['app_root'], ENT_QUOTES, 'UTF-8') : '';
    echo "                    <button class=\"btn modern-header-btn\" onclick=\"window.location.href='" . $appRoot . "?page=register'\">
                        <i class=\"fas fa-user-edit me-2\"></i>Register
                    </button>";
});
