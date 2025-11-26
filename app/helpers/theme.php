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
            // Attempt to load per-user theme from DB if user is logged in and userObject is available
            if (Session::isValidSession() && isset($_SESSION['user_id']) && isset($GLOBALS['userObject']) && is_object($GLOBALS['userObject']) && method_exists($GLOBALS['userObject'], 'getUserTheme')) {
                try {
                    $dbTheme = $GLOBALS['userObject']->getUserTheme((int)$_SESSION['user_id']);
                    if ($dbTheme && isset(self::$config['available_themes'][$dbTheme]) && self::themeExists($dbTheme)) {
                        // Set session and current theme to the user's stored preference
                        $_SESSION['theme'] = $dbTheme;
                        self::$currentTheme = $dbTheme;
                    }
                } catch (\Throwable $e) {
                    // Ignore and continue to default fallback
                }
            }

            // Fall back to default theme if still not determined
            if (self::$currentTheme === null) {
                self::$currentTheme = self::$config['active_theme'];
            }
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
    public static function setCurrentTheme(string $themeName, bool $persist = true): bool
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

        // Persist per-user preference in DB when available and requested
        if ($persist && Session::isValidSession() && isset($_SESSION['user_id'])) {
            // Try to use existing user object if available
            if (isset($GLOBALS['userObject']) && is_object($GLOBALS['userObject']) && method_exists($GLOBALS['userObject'], 'setUserTheme')) {
                try {
                    $GLOBALS['userObject']->setUserTheme((int)$_SESSION['user_id'], $themeName);
                } catch (\Throwable $e) {
                    // Non-fatal: keep session theme even if DB save fails
                    error_log('Failed to persist user theme: ' . $e->getMessage());
                }
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
     * Get descriptive metadata for a theme.
     *
     * @param string $themeId
     * @return array{name:string,description:string,version:string,author:string,tags:array}
     */
    public static function getThemeMetadata(string $themeId): array
    {
        static $cache = [];
        if (isset($cache[$themeId])) {
            return $cache[$themeId];
        }

        $config = self::getConfig();
        $defaults = $config['default_config'] ?? [];
        $availableEntry = $config['available_themes'][$themeId] ?? null;

        $metadata = [
            'name' => is_array($availableEntry) ? ($availableEntry['name'] ?? ucfirst($themeId)) : ($availableEntry ?? ucfirst($themeId)),
            'description' => $defaults['description'] ?? '',
            'version' => $defaults['version'] ?? '',
            'author' => $defaults['author'] ?? '',
            'tags' => [],
            'type' => $themeId === 'default' ? 'Core built-in' : 'Custom',
            'path' => $themeId === 'default' ? 'app/templates' : ('themes/' . $themeId),
            'last_modified' => null,
            'file_count' => null
        ];

        if (is_array($availableEntry)) {
            $metadata = array_merge($metadata, array_intersect_key($availableEntry, array_flip(['name', 'description', 'version', 'author', 'tags'])));
        }

        if ($themeId !== 'default') {
            $themesDir = rtrim($config['paths']['themes'] ?? (__DIR__ . '/../../themes'), '/');
            $themeConfigPath = $themesDir . '/' . $themeId . '/config.php';
            if (file_exists($themeConfigPath)) {
                $themeConfig = require $themeConfigPath;
                if (is_array($themeConfig)) {
                    $metadata = array_merge($metadata, array_intersect_key($themeConfig, array_flip(['name', 'description', 'version', 'author', 'tags'])));
                }
            }
        }

        if (empty($metadata['description'])) {
            $metadata['description'] = $defaults['description'] ?? 'A Jilo Web theme';
        }
        if (empty($metadata['version'])) {
            $metadata['version'] = $defaults['version'] ?? '1.0.0';
        }
        if (empty($metadata['author'])) {
            $metadata['author'] = $defaults['author'] ?? 'Lindeas';
        }

        if (empty($metadata['tags']) || !is_array($metadata['tags'])) {
            $metadata['tags'] = [];
        }

        $paths = $config['paths'] ?? [];
        if ($themeId === 'default') {
            $absolutePath = realpath($paths['templates'] ?? (__DIR__ . '/../templates')) ?: null;
        } else {
            $absolutePath = self::getThemePath($themeId);
        }

        if ($absolutePath && is_dir($absolutePath)) {
            [$lastModified, $fileCount] = self::getDirectoryStats($absolutePath);
            if ($lastModified !== null) {
                $metadata['last_modified'] = $lastModified;
            }
            if ($fileCount > 0) {
                $metadata['file_count'] = $fileCount;
            }
        }

        return $cache[$themeId] = $metadata;
    }


    /**
     * Calculate directory statistics for a theme folder.
     */
    private static function getDirectoryStats(string $path): array
    {
        $latest = null;
        $count = 0;

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }
                $count++;
                $mtime = $fileInfo->getMTime();
                if ($latest === null || $mtime > $latest) {
                    $latest = $mtime;
                }
            }
        } catch (\Throwable $e) {
            return [null, 0];
        }

        return [$latest, $count];
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
