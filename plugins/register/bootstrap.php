<?php

// Add to allowed URLs
register_hook('filter_allowed_urls', function($urls) {
    $urls[] = 'register';
    return $urls;
});

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
    echo '<li><a href="?page=register">register</a></li>';
});
