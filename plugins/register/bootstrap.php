<?php

// Register plugin bootstrap
// (here we add any plugin autoloader, if needed)

// List here all the controllers in "/controllers/" that we need as pages
$GLOBALS['plugin_controllers']['register'] = [
    'register'
];

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
    echo "                    <button class=\"btn modern-header-btn\" onclick=\"window.location.href='" . htmlspecialchars($app_root) . "?page=register'\">
                        <i class=\"fas fa-user-edit me-2\"></i>Register
                    </button>";
});
