<?php

global $config;
$siteName = (string)$config['site_name'];

return [
    'name' => 'Modern theme',
    'description' => sprintf('Example theme. A modern, clean theme for %s.', $siteName),
    'version' => '1.0.0',
    'author' => 'Lindeas Inc.',
    'screenshot' => 'screenshot.png',
    'options' => [
        // Theme-specific options can be defined here
    ]
];
