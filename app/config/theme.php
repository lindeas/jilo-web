<?php

return [
    // Active theme (can be overridden by user preference)
    'active_theme' => 'default',

    // Available themes with their display names
    'available_themes' => [
        'default' => 'Default built-in theme',
        'modern' => 'Modern theme',
        'retro' => 'Alternative retro theme'
    ],

    // Path configurations
    'paths' => [
        // Base directory for all external themes
        'themes' => __DIR__ . '/../../themes',

        // Default templates location (built-in fallback)
        'templates' => __DIR__ . '/../templates',

        // Public assets directory (built-in fallback)
        'public' => __DIR__ . '/../../public_html'
    ],

    // Theme configuration defaults
    'default_config' => [
        'name' => 'Unnamed Theme',
        'description' => 'A Jilo Web theme',
        'version' => '1.0.0',
        'author' => 'Lindeas Inc.',
        'screenshot' => 'screenshot.png',
        'options' => []
    ]
];
