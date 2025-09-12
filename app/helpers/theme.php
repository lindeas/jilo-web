<?php

/**
 * Theme Helper
 *
 * Handles theme management and template/asset loading for the application.
 * Supports multiple themes with fallback to default theme when needed.
 * The default theme uses app/templates and public_html/static as fallbacks/
 */

namespace App\Helpers;

use Exception;

// Include Session class
require_once __DIR__ . '/../classes/session.php';
use Session;

class Theme
{
    /**
     * @var array Theme configuration
     */
    private static $config;


    /**
     * Get the theme configuration
     *
     * @return array
     */
    public static function getConfig()
    {
        $configFile = __DIR__ . '/../config/theme.php';

        // Create default config if it doesn't exist
        if (!file_exists($configFile)) {
            $configDir = dirname($configFile);
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }

            // Generate the config file with proper formatting
            $configContent = <<<'EOT'
<?php

/**
 * Theme Configuration
 *
 * This file is auto-generated. Do not edit it manually.
 * Use the theme management interface to modify theme settings.
 */

return [
    // Active theme (can be overridden by user preference)
    'active_theme' => 'modern',

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

EOT;

            file_put_contents($configFile, $configContent);
        }

        // Load the configuration
        self::$config = require $configFile;
        return self::$config;
    }


    /**
     * @var string Current theme name
     */
    private static $currentTheme;


    /**
     * Initialize the theme system
     */
    public static function init()
    {
        // Only load config if not already loaded
        if (self::$config === null) {
            try {
                self::getConfig(); // This will create default config if needed
            } catch (Exception $e) {
                error_log('Failed to load theme configuration: ' . $e->getMessage());
                // Fallback to default configuration
                self::$config = [
                    'active_theme' => 'modern',
                    'available_themes' => [
                        'modern' => ['name' => 'Modern'],
                        'retro' => ['name' => 'Retro']
                    ]
                ];
            }
        }

        self::$currentTheme = self::getCurrentThemeName();
    }


    /**
     * Get the current theme name
     *
     * @return string
     */
    public static function getCurrentThemeName()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            Session::startSession();
        }

        // Check if already determined
        if (self::$currentTheme !== null) {
            return self::$currentTheme;
        }

        // Try to get from session first
        $sessionTheme = isset($_SESSION['theme']) ? $_SESSION['theme'] : null;
        if ($sessionTheme && isset(self::$config['available_themes'][$sessionTheme])) {
            self::$currentTheme = $sessionTheme;
        } else {
            // Fall back to default theme
            self::$currentTheme = self::$config['active_theme'];
        }

        return self::$currentTheme;
    }


    /**
     * Get the URL for a theme asset
     *
     * @param string $themeId Theme ID
     * @param string $assetPath Path to the asset relative to theme directory (e.g., 'css/style.css')
     * @return string|null URL to the asset or null if not found
     */
    public static function getAssetUrl($themeId, $assetPath = '')
    {
        // Clean and validate the asset path
        $assetPath = ltrim($assetPath, '/');
        if (empty($assetPath)) {
            return null;
        }

        // Only allow alphanumeric, hyphen, underscore, dot, and forward slash
        if (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $assetPath)) {
            return null;
        }

        // Prevent directory traversal
        if (strpos($assetPath, '..') !== false) {
            return null;
        }

        $fullPath = __DIR__ . "/../../themes/$themeId/$assetPath";
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return null;
        }

        // Generate URL that goes through index.php
        global $app_root;
        // Remove any trailing slash from app_root to avoid double slashes
        $baseUrl = rtrim($app_root, '/');
        return "$baseUrl/?page=theme-asset&theme=" . urlencode($themeId) . "&path=" . urlencode($assetPath);
    }


    /**
     * Set the current theme for the session
     *
     * @param string $themeName
     * @return bool
     */
    public static function setCurrentTheme(string $themeName): bool
    {
        if (!self::themeExists($themeName)) {
            return false;
        }

        // Update session
        if (Session::isValidSession()) {
            $_SESSION['theme'] = $themeName;
        } else {
            return false;
        }

        // Clear the current theme cache
        self::$currentTheme = null;

        // Update config file
        $configFile = __DIR__ . '/../config/theme.php';

        // Check if config file exists and is writable
        if (!file_exists($configFile)) {
            error_log("Theme config file not found: $configFile");
            return false;
        }

        if (!is_writable($configFile)) {
            error_log("Theme config file is not writable: $configFile");
            if (isset($GLOBALS['feedback_messages'])) {
                $GLOBALS['feedback_messages'][] = [
                    'type' => 'error',
                    'message' => 'Cannot save theme preference: configuration file is not writable.'
                ];
            }
            return false;
        }

        $config = file_get_contents($configFile);
        if ($config === false) {
            error_log("Failed to read theme config file: $configFile");
            return false;
        }

        // Update the active_theme in the config
        $newConfig = preg_replace(
            "/'active_theme'\s*=>\s*'[^']*'/",
            "'active_theme' => '" . addslashes($themeName) . "'",
            $config
        );

        if ($newConfig !== $config) {
            if (file_put_contents($configFile, $newConfig) === false) {
                error_log("Failed to write to theme config file: $configFile");
                if (isset($GLOBALS['feedback_messages'])) {
                    $GLOBALS['feedback_messages'][] = [
                        'type' => 'error',
                        'message' => 'Failed to save theme preference due to a system error.'
                    ];
                }
                return false;
            }
        }

        self::$currentTheme = $themeName;
        return true;
    }


    /**
     * Check if a theme exists
     *
     * @param string $themeName
     * @return bool
     */
    public static function themeExists(string $themeName): bool
    {
        // Default theme always exists as it uses core templates
        if ($themeName === 'default') {
            return true;
        }

        $themePath = self::getThemePath($themeName);
        return is_dir($themePath) && file_exists("$themePath/config.php");
    }


    /**
     * Get the path to a theme
     *
     * @param string|null $themeName
     * @return string
     */
    public static function getThemePath(?string $themeName = null): string
    {
        $themeName = $themeName ?? self::getCurrentThemeName();
        $config = self::getConfig();
        return rtrim($config['paths']['themes'], '/') . "/$themeName";
    }


    /**
     * Get the URL for a theme asset
     *
     * @param string $path
     * @param bool $includeVersion
     * @return string
     */
    public static function asset($path, $includeVersion = false)
    {
        $themeName = self::getCurrentThemeName();
        $config = self::getConfig();
        $baseUrl = rtrim($GLOBALS['app_root'] ?? '', '/');

        // For non-default themes, use theme assets
        if ($themeName !== 'default') {
            $assetPath = "/themes/{$themeName}/assets/" . ltrim($path, '/');

            // Add version query string for cache busting
            if ($includeVersion) {
                $version = self::getThemeVersion($themeName);
                $assetPath .= (strpos($assetPath, '?') !== false ? '&' : '?') . 'v=' . $version;
            }
        } else {
            // For default theme, use public_html directly
            $assetPath = '/' . ltrim($path, '/');
        }

        return $baseUrl . $assetPath;
    }


    /**
     * Include a theme template file
     *
     * @param string $template Template name without .php extension
     * @return void
     */
    public static function include($template)
    {
        global $config;
        $config = $config ?? [];

        $themeConfig = self::getConfig();
        $themeName = self::getCurrentThemeName();

        // We need this here, otherwise because this helper
        // between index and the views breaks the session vars
        extract($GLOBALS, EXTR_SKIP | EXTR_REFS);

        // Ensure config is always available in templates
        $config = array_merge($config, $themeConfig);

        // For non-default themes, look in the theme directory first
        if ($themeName !== 'default') {
            $themePath = $config['paths']['themes'] . '/' . $themeName . '/views/' . $template . '.php';
            if (file_exists($themePath)) {
                include $themePath;
                return;
            }
        }

        // Fallback to default template location
        $defaultPath = $config['paths']['templates'] . '/' . $template . '.php';
        if (file_exists($defaultPath)) {
            include $defaultPath;
            return;
        }

        // Log error if template not found
        error_log("Template not found: {$template} in theme: {$themeName}");
    }


    /**
     * Get all available themes
     *
     * @return array
     */
    public static function getAvailableThemes(): array
    {
        $config = self::getConfig();
        $availableThemes = $config['available_themes'] ?? [];
        $themes = [];

        // Add default theme if not already present
        if (!isset($availableThemes['default'])) {
            $availableThemes['default'] = 'Default built-in theme';
        }

        // Verify each theme exists and has a config file
        $themesDir = $config['paths']['themes'] ?? (__DIR__ . '/../../themes');
        foreach ($availableThemes as $id => $name) {
            if ($id === 'default' || (is_dir("$themesDir/$id") && file_exists("$themesDir/$id/config.php"))) {
                $themes[$id] = $name;
            }
        }

        return $themes;
    }
}

// Initialize the theme system
Theme::init();
