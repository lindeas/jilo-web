<?php

namespace App\Core;

class HookDispatcher
{
    /**
     * Stores all registered hooks and their callbacks.
     * @var array<string, array<callable>>
     */
    private static array $hooks = [];

    /**
     * Register a callback for a given hook.
     */
    public static function register(string $hook, callable $callback): void
    {
        if (!isset(self::$hooks[$hook])) {
            self::$hooks[$hook] = [];
        }
        self::$hooks[$hook][] = $callback;
    }

    /**
     * Dispatch all callbacks for the specified hook.
     */
    public static function dispatch(string $hook, array $context = []): void
    {
        if (!empty(self::$hooks[$hook])) {
            foreach (self::$hooks[$hook] as $callback) {
                call_user_func($callback, $context);
            }
        }
    }

    /**
     * Apply filters for a hook key, passing a value through all callbacks.
     * Each callback should accept the value and return a modified value.
     *
     * @param string $hook
     * @param mixed $value
     * @return mixed
     */
    public static function applyFilters(string $hook, $value)
    {
        if (!empty(self::$hooks[$hook])) {
            foreach (self::$hooks[$hook] as $callback) {
                $value = call_user_func($callback, $value);
            }
        }
        return $value;
    }
}
