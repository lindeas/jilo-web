<?php

namespace App\Core;

class ConfigLoader
{
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
        return require $configFile;
    }
}
