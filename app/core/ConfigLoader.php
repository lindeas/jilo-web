<?php

namespace App\Core;

class ConfigLoader
{
    /**
     * @var string|null
     */
    private static $configPath = null;

    /**
     * Load configuration array from a set of possible file locations.
     *
     * @param string[] $locations
     * @return array
     */
    public static function loadConfig(array $locations): array
    {
        $configFile = null;
        foreach ($locations as $location) {
            if (file_exists($location)) {
                $configFile = $location;
                break;
            }
        }
        if (!$configFile) {
            die('Config file not found');
        }
        self::$configPath = $configFile;
        return require $configFile;
    }

    /**
     * @return string|null
     */
    public static function getConfigPath(): ?string
    {
        return self::$configPath;
    }
}
