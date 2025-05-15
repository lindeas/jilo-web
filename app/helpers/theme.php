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

class Theme
{
    /**
     * @var array Theme configuration
     */
    private static $config;

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
     * Include a theme template
     *
     * @param string $template
     * @param array $data
     * @return void
     */
    public static function include($template, $data = [])
    {
        $themeName = self::getCurrentThemeName();
        $config = self::getConfig();

        // For non-default themes, look in the theme directory
        if ($themeName !== 'default') {
            $themePath = $config['paths']['themes'] . '/' . $themeName . '/views/' . $template . '.php';
            if (file_exists($themePath)) {
                extract($data);
                include $themePath;
                return;
            }

            // Fallback to default theme if template not found in custom theme
            $legacyPath = $config['paths']['templates'] . '/' . $template . '.php';
            if (file_exists($legacyPath)) {
                extract($data);
                include $legacyPath;
                return;
            }
        } else {
            // Default theme uses app/templates
            $legacyPath = $config['paths']['templates'] . '/' . $template . '.php';
            if (file_exists($legacyPath)) {
                extract($data);
                include $legacyPath;
                return;
            }
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
        $themes = [];
        $themesDir = self::$config['path'] ?? (__DIR__ . '/../../themes');

        if (!is_dir($themesDir)) {
            return [];
        }

        foreach (scandir($themesDir) as $item) {
            if ($item === '.' || $item === '..' || !is_dir("$themesDir/$item")) {
                continue;
            }

            $configFile = "$themesDir/$item/config.php";
            if (file_exists($configFile)) {
                $config = include $configFile;
                $themes[$item] = $config['name'] ?? ucfirst($item);
            }
        }

        return $themes;
    }
}

// Initialize the theme system
Theme::init();
