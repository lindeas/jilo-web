<?php

namespace App\Core;

class PluginManager
{
    /**
     * Loads all enabled plugins from the given directory.
     *
     * @param string $pluginsDir
     * @return array<string, array{path: string, meta: array}>
     */
    public static function load(string $pluginsDir): array
    {
        $enabled = [];
        foreach (glob($pluginsDir . '*', GLOB_ONLYDIR) as $pluginPath) {
            $manifest = $pluginPath . '/plugin.json';
            if (!file_exists($manifest)) {
                continue;
            }
            $meta = json_decode(file_get_contents($manifest), true);
            if (empty($meta['enabled'])) {
                continue;
            }
            $name = basename($pluginPath);
            $enabled[$name] = [
                'path' => $pluginPath,
                'meta' => $meta,
            ];
            $bootstrap = $pluginPath . '/bootstrap.php';
            if (file_exists($bootstrap)) {
                include_once $bootstrap;
            }
        }
        return $enabled;
    }
}
