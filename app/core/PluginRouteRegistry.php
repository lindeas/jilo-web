<?php

namespace App\Core;

/**
 * Registry for plugin route prefixes/dispatchers. Allows plugins to handle
 * sub-actions without registering dozens of standalone controllers.
 */
final class PluginRouteRegistry
{
    /** @var array<string, array{dispatcher: mixed, access?: string, defaults?: array, plugin?: string}> */
    private static array $prefixes = [];

    /**
     * Register a route prefix for a plugin.
     *
     * @param string $prefix Query parameter value for "page" (e.g. "calls").
     * @param array  $definition dispatcher callable/class plus optional metadata.
     */
    public static function registerPrefix(string $prefix, array $definition): void
    {
        $key = strtolower(trim($prefix));
        if ($key === '') {
            return;
        }

        $dispatcher = $definition['dispatcher'] ?? null;
        if (!is_callable($dispatcher) && !(is_string($dispatcher) && $dispatcher !== '')) {
            return;
        }

        $meta = [
            'dispatcher' => $dispatcher,
            'access' => strtolower((string)($definition['access'] ?? 'private')),
            'defaults' => is_array($definition['defaults'] ?? null) ? $definition['defaults'] : [],
            'plugin' => $definition['plugin'] ?? null,
        ];

        self::$prefixes[$key] = $meta;
        if (!isset($GLOBALS['plugin_route_prefixes']) || !is_array($GLOBALS['plugin_route_prefixes'])) {
            $GLOBALS['plugin_route_prefixes'] = [];
        }
        $GLOBALS['plugin_route_prefixes'][$key] = $meta;
    }

    /**
     * Return a registered route definition, if any.
     */
    public static function match(string $prefix): ?array
    {
        $key = strtolower(trim($prefix));
        return self::$prefixes[$key] ?? null;
    }

    /**
     * Append registered prefixes to the allowed pages list.
     */
    public static function injectAllowedPages(array $allowed): array
    {
        return array_values(array_unique(array_merge($allowed, array_keys(self::$prefixes))));
    }

    /**
     * Append any public prefixes to the public pages list.
     */
    public static function injectPublicPages(array $public): array
    {
        foreach (self::$prefixes as $prefix => $meta) {
            if (($meta['access'] ?? 'private') === 'public') {
                $public[] = $prefix;
            }
        }
        return array_values(array_unique($public));
    }

    /**
     * Dispatch the provided prefix using its registered handler.
     *
     * The dispatcher can be:
     *  - A callable accepting ($action, array $context)
     *  - A class name with a handle($action, array $context): bool method
     *
     * Returning `false` allows core routing to continue. Any other return value
     * (including null) is treated as handled.
     */
    public static function dispatch(string $prefix, array $context = []): bool
    {
        $route = self::match($prefix);
        if (!$route) {
            return false;
        }

        $action = $context['action']
            ?? ($context['request']['action'] ?? null)
            ?? ($route['defaults']['action'] ?? 'index');
        $context['action'] = $action;

        $dispatcher = $route['dispatcher'];
        $handled = null;

        if (is_string($dispatcher) && class_exists($dispatcher)) {
            $instance = new $dispatcher();
            if (method_exists($instance, 'handle')) {
                $handled = $instance->handle($action, $context);
            }
        } elseif (is_callable($dispatcher)) {
            $handled = call_user_func($dispatcher, $action, $context);
        }

        return $handled !== false;
    }

    /**
     * Expose current registry (useful for debugging or admin UIs).
     */
    public static function all(): array
    {
        return self::$prefixes;
    }

    /**
     * Reset registry (primarily for unit tests).
     */
    public static function reset(): void
    {
        self::$prefixes = [];
        $GLOBALS['plugin_route_prefixes'] = [];
    }
}
