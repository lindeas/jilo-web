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
        if (self::$config === null) {
            self::init();
        }
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
        self::$config = require __DIR__ . '/../config/theme.php';
        self::$currentTheme = self::getCurrentThemeName();
    }

    /**
     * Get the current theme name
     *
     * @return string
     */
    public static function getCurrentThemeName()
    {
        // Check if already determined
        if (self::$currentTheme !== null) {
            return self::$currentTheme;
        }

        // Get from session if available
        if (Session::isValidSession() && ($theme = Session::get('user_theme'))) {
            if (self::themeExists($theme)) {
                self::$currentTheme = $theme;
                return $theme;
            }
        }

        // Default to 'default' theme which uses app/templates
        self::$currentTheme = 'default';
        return 'default';
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

        if (Session::isValidSession()) {
            Session::set('user_theme', $themeName);
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
        $themeName = self::getCurrentThemeName();
        $config = self::getConfig();

        // Import all global variables into local scope
        // We need this here, otherwise because this helper
        // between index and the views breaks the session vars
        extract($GLOBALS, EXTR_SKIP | EXTR_REFS);

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
            if ($id === 'default' || 
                (is_dir("$themesDir/$id") && file_exists("$themesDir/$id/config.php"))) {
                $themes[$id] = $name;
            }
        }

        return $themes;
    }
}

// Initialize the theme system
Theme::init();
