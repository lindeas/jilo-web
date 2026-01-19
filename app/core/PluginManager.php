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
            if (!self::isEnabled($name)) {
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
        if (!self::isEnabled($plugin)) {
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
            if (!self::isEnabled($dependency)) {
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
     * Persists a plugin's enabled flag to the database settings table.
     * Note: This method no longer requires write access to plugin.json files.
     */
    public static function setEnabled(string $plugin, bool $enabled): bool
    {
        if (!isset(self::$catalog[$plugin])) {
            return false;
        }

        global $db;
        if (!$db instanceof PDO) {
            return false;
        }

        try {
            // Update or insert plugin setting in database
            $stmt = $db->prepare(
                'INSERT INTO settings (`key`, `value`, updated_at) 
                 VALUES (:key, :value, NOW()) 
                 ON DUPLICATE KEY UPDATE `value` = :value, updated_at = NOW()'
            );
            $key = 'plugin_enabled_' . $plugin;
            $value = $enabled ? '1' : '0';
            $stmt->execute([':key' => $key, ':value' => $value]);

            // Clear loaded cache if disabling
            if (!$enabled && isset(self::$loaded[$plugin])) {
                unset(self::$loaded[$plugin]);
            }

            return true;
        } catch (PDOException $e) {
            // Log the actual error for debugging
            error_log('PluginManager::setEnabled failed for ' . $plugin . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a plugin is enabled from database settings.
     */
    public static function isEnabled(string $plugin): bool
    {
        if (!isset(self::$catalog[$plugin])) {
            return false;
        }

        global $db;
        if ($db instanceof PDO) {
            try {
                $stmt = $db->prepare('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1');
                $key = 'plugin_enabled_' . $plugin;
                $stmt->execute([':key' => $key]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result !== false) {
                    return $result['value'] === '1';
                }
            } catch (PDOException $e) {
                // Log error but return false
                error_log('PluginManager::isEnabled database error for ' . $plugin . ': ' . $e->getMessage());
            }
        }
        
        // Default to disabled if no database entry or database unavailable
        return false;
    }

    /**
     * Install plugin by running its migrations.
     */
    public static function install(string $plugin): bool
    {
        if (!isset(self::$catalog[$plugin])) {
            return false;
        }

        $pluginPath = self::$catalog[$plugin]['path'];
        $bootstrapPath = $pluginPath . '/bootstrap.php';
        
        if (!file_exists($bootstrapPath)) {
            return false;
        }

        try {
            // Include bootstrap to run migrations
            include_once $bootstrapPath;
            
            // Look for migration function
            $migrationFunction = str_replace('-', '_', $plugin) . '_ensure_tables';
            if (function_exists($migrationFunction)) {
                $migrationFunction();
                return true;
            }
            
            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Purge plugin by dropping its tables and removing settings.
     */
    public static function purge(string $plugin): bool
    {
        if (!isset(self::$catalog[$plugin])) {
            return false;
        }

        global $db;
        if (!$db instanceof PDO) {
            return false;
        }

        try {
            // First disable the plugin
            self::setEnabled($plugin, false);
            
            // Remove plugin settings
            $stmt = $db->prepare('DELETE FROM settings WHERE `key` LIKE :pattern');
            $stmt->execute([':pattern' => 'plugin_enabled_' . $plugin]);
            
            // Drop plugin-specific tables (user_pro_* tables for this plugin)
            $stmt = $db->prepare('SHOW TABLES LIKE "user_pro_%"');
            $stmt->execute();
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            
            foreach ($tables as $table) {
                // Check if this table belongs to the plugin by checking its migration file
                $migrationFile = self::$catalog[$plugin]['path'] . '/migrations/create_' . $plugin . '_tables.sql';
                if (file_exists($migrationFile)) {
                    $migrationContent = file_get_contents($migrationFile);
                    if (strpos($migrationContent, $table) !== false) {
                        $db->exec("DROP TABLE IF EXISTS `$table`");
                    }
                }
            }
            
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
