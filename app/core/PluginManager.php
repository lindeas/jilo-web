<?php

namespace App\Core;

class PluginManager
{
    /** @var array<string, array{path: string, meta: array}> */
    private static array $catalog = [];

    /** @var array<string, array{path: string, meta: array}>> */
    private static array $loaded = [];

    /** @var array<string, array<int, string>> */
    private static array $dependencyErrors = [];

    /**
     * Loads all enabled plugins from the given directory.
     * Enforces declared dependencies before bootstrapping each plugin.
     *
     * @param string $pluginsDir
     * @return array<string, array{path: string, meta: array}>
     */
    public static function load(string $pluginsDir): array
    {
        self::$catalog = self::scanCatalog($pluginsDir);
        self::$loaded = [];
        self::$dependencyErrors = [];

        foreach (self::$catalog as $name => $info) {
            if (empty($info['meta']['enabled'])) {
                continue;
            }
            self::resolve($name);
        }

        $GLOBALS['plugin_dependency_errors'] = self::$dependencyErrors;

        return self::$loaded;
    }

    /**
     * @param string $pluginsDir
     * @return array<string, array{path: string, meta: array}>
     */
    private static function scanCatalog(string $pluginsDir): array
    {
        $catalog = [];
        foreach (glob(rtrim($pluginsDir, '/'). '/*', GLOB_ONLYDIR) as $pluginPath) {
            $manifest = $pluginPath . '/plugin.json';
            if (!file_exists($manifest)) {
                continue;
            }
            $meta = json_decode(file_get_contents($manifest), true);
            if (!is_array($meta)) {
                $meta = [];
            }
            $name = basename($pluginPath);
            $catalog[$name] = [
                'path' => $pluginPath,
                'meta' => $meta,
            ];
        }

        return $catalog;
    }

    /**
     * Recursively resolves a plugin and its dependencies.
     */
    private static function resolve(string $plugin, array $stack = []): bool
    {
        if (isset(self::$loaded[$plugin])) {
            return true;
        }

        if (!isset(self::$catalog[$plugin])) {
            return false;
        }

        if (in_array($plugin, $stack, true)) {
            self::$dependencyErrors[$plugin][] = 'Circular dependency detected: ' . implode(' -> ', array_merge($stack, [$plugin]));
            return false;
        }

        $meta = self::$catalog[$plugin]['meta'];
        if (empty($meta['enabled'])) {
            return false;
        }

        $dependencies = $meta['dependencies'] ?? [];
        if (!is_array($dependencies)) {
            $dependencies = [$dependencies];
        }

        $stack[] = $plugin;
        foreach ($dependencies as $dependency) {
            $dependency = trim((string)$dependency);
            if ($dependency === '') {
                continue;
            }
            if (!isset(self::$catalog[$dependency])) {
                self::$dependencyErrors[$plugin][] = sprintf('Missing dependency "%s"', $dependency);
                continue;
            }
            if (empty(self::$catalog[$dependency]['meta']['enabled'])) {
                self::$dependencyErrors[$plugin][] = sprintf('Dependency "%s" is disabled', $dependency);
                continue;
            }
            if (!self::resolve($dependency, $stack)) {
                self::$dependencyErrors[$plugin][] = sprintf('Dependency "%s" failed to load', $dependency);
            }
        }
        array_pop($stack);

        if (!empty(self::$dependencyErrors[$plugin])) {
            return false;
        }

        $bootstrap = self::$catalog[$plugin]['path'] . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        self::$loaded[$plugin] = self::$catalog[$plugin];

        return true;
    }

    /**
     * Returns the scanned plugin catalog (enabled and disabled).
     *
     * @return array<string, array{path: string, meta: array}>
     */
    public static function getCatalog(): array
    {
        return self::$catalog;
    }

    /**
     * Returns all plugins that successfully loaded (dependencies satisfied).
     *
     * @return array<string, array{path: string, meta: array}>
     */
    public static function getLoaded(): array
    {
        return self::$loaded;
    }

    /**
     * Returns dependency validation errors collected during load.
     *
     * @return array<string, array<int, string>>
     */
    public static function getDependencyErrors(): array
    {
        return self::$dependencyErrors;
    }

    /**
     * Persists a plugin's enabled flag back to its manifest.
     */
    public static function setEnabled(string $plugin, bool $enabled): bool
    {
        if (!isset(self::$catalog[$plugin])) {
            return false;
        }

        $manifestPath = self::$catalog[$plugin]['path'] . '/plugin.json';
        if (!is_file($manifestPath) || !is_readable($manifestPath) || !is_writable($manifestPath)) {
            return false;
        }

        $raw = file_get_contents($manifestPath);
        $data = json_decode($raw ?: '', true);
        if (!is_array($data)) {
            $data = self::$catalog[$plugin]['meta'];
        }

        $data['enabled'] = $enabled;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        if (file_put_contents($manifestPath, $json, LOCK_EX) === false) {
            return false;
        }

        self::$catalog[$plugin]['meta'] = $data;
        if (!$enabled && isset(self::$loaded[$plugin])) {
            unset(self::$loaded[$plugin]);
        }

        return true;
    }
}
