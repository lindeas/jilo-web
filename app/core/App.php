<?php

namespace App;

/**
 * Minimal application API
 * we use it expose and access core services from plugins (and legacy code).
 */
final class App
{
    /** @var array<string, mixed> */
    private static array $services = [];

    /**
     * Register or override a service value.
     */
    public static function set(string $key, $value): void
    {
        self::$services[$key] = $value;
    }

    /**
     * Clear one or all registered services.
     *
     * Primarily used by unit tests to avoid cross-test pollution.
     */
    public static function reset(?string $key = null): void
    {
        if ($key === null) {
            self::$services = [];
            return;
        }
        unset(self::$services[$key]);
    }

    /**
     * Determine whether a value is registered.
     */
    public static function has(string $key): bool
    {
        if (array_key_exists($key, self::$services)) {
            return true;
        }
        return self::fallback($key) !== null;
    }

    /**
     * Retrieve a registered value.
     * Falls back to legacy globals when no explicit service was registered.
     */
    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$services)) {
            return self::$services[$key];
        }
        $fallback = self::fallback($key);
        return $fallback !== null ? $fallback : $default;
    }

    /**
     * Convenience accessor for the database connection.
     */
    public static function db()
    {
        return self::get('db');
    }

    /**
     * Convenience accessor for the configuration array.
     */
    public static function config(): array
    {
        $config = self::get('config', []);
        return is_array($config) ? $config : [];
    }

    /**
     * Convenience accessor for the authenticated user object, if any.
     */
    public static function user()
    {
        return self::get('user');
    }

    /**
     * Basic fallback bridge for legacy globals.
     */
    private static function fallback(string $key)
    {
        switch ($key) {
            case 'config':
                return $GLOBALS['config'] ?? null;
            case 'config_path':
                return $GLOBALS['config_file'] ?? null;
            case 'db':
                return $GLOBALS['db'] ?? null;
            case 'user':
            case 'user_object':
                return $GLOBALS['userObject'] ?? null;
            case 'user_id':
                return $GLOBALS['userId'] ?? null;
            case 'logger':
                return $GLOBALS['logObject'] ?? null;
            case 'app_root':
                return $GLOBALS['app_root'] ?? null;
            default:
                return null;
        }
    }
}
