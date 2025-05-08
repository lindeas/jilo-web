<?php

// Logs plugin bootstrap
// (here we add any plugin autoloader, if needed)

// List here all the controllers in "/controllers/" that we need as pages
$GLOBALS['plugin_controllers']['logs'] = [
    'logs'
];

// Logger plugin bootstrap
register_hook('logger.system_init', function(array $context) {
    // Load plugin-specific LoggerFactory class
    require_once __DIR__ . '/models/LoggerFactory.php';
    [$logger, $userIP] = LoggerFactory::create($context['db']);

    // Expose to globals for routing logic
    $GLOBALS['logObject'] = $logger;
    $GLOBALS['user_IP']   = $userIP;
});

// Configuration for top menu injection
define('LOGS_MAIN_MENU_SECTION', 'main'); // section of the top menu
define('LOGS_MAIN_MENU_POSITION', 20);    // lower = earlier in menu
register_hook('main_menu', function($ctx) {
    $section = defined('LOGS_MAIN_MENU_SECTION') ? LOGS_MAIN_MENU_SECTION : 'main';
    $position = defined('LOGS_MAIN_MENU_POSITION') ? LOGS_MAIN_MENU_POSITION : 100;
    // We use $section/$position for sorting/insertion logic in the menu template
    echo '
                        <a class="dropdown-item" href="?page=logs">
                            <i class="fas fa-list"></i>Logs
                        </a>';
});
