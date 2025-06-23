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
        // Always reload the config to get the latest changes
        self::$config = require __DIR__ . '/../config/theme.php';
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
            self::$config = require __DIR__ . '/../config/theme.php';
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

        // Use the router to generate the URL
        global $app_root;
        return "$app_root/app/helpers/theme-asset.php?theme=" . urlencode($themeId) . "&path=" . urlencode($assetPath);
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
            $_SESSION['user_theme'] = $themeName;
        } else {
            return false;
        }

        // Update config file
        $configFile = __DIR__ . '/../config/theme.php';
        if (file_exists($configFile) && is_writable($configFile)) {
            $config = file_get_contents($configFile);
            // Update the active_theme in the config
            $newConfig = preg_replace(
                "/'active_theme'\s*=>\s*'[^']*'/",
                "'active_theme' => '" . addslashes($themeName) . "'",
                $config
            );

            if ($newConfig !== $config) {
                if (file_put_contents($configFile, $newConfig) === false) {
                    return false;
                }
            }
            self::$currentTheme = $themeName;
            return true;
        }
        return false;
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
