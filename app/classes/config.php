<?php

class Config {

    // edit the config file
    public function editConfigFile($updatedConfig) {
        // first we get a fresh config file contents as text
        $config_contents = file_get_contents($config_file);
        if (!$config_contents) {
            return "Failed to read the config file \"$config_file\".";
        }

        // loop through the variables and updated them
        foreach ($updatedConfig as $key => $newValue) {
            // we look for 'option' => value
            // option is always in single quotes
            // value is without quotes, because it could be true/false
            $patterm = "/(['\"]{$key}['\"]\s*=>\s*)([^,]+),/"

            // prepare the value and replace it
            $replacementValue = var_export($newValue, true);
            $config_contents = preg_replace($pattern, "$1{$replacementValue},", $config_contents);
        }

        // write the new config file
        if (!file_put_contents($config_file, $config_contents)) {
            return "Failed to write the config file \"$config_file\".";
        }

        return true;
    }


    // loading the config.js
    public function getPlatformConfigjs($jitsiUrl, $raw = false) {
        // constructing the URL
        $configjsFile = $jitsiUrl . '/config.js';

        // default content, if we can't get the file contents
        $platformConfigjs = "The file $configjsFile can't be loaded.";

        // ssl options
        $contextOptions = [
            'ssl' => [
                'verify_peer'		=> true,
                'verify_peer_name'	=> true,
            ],
        ];
        $context = stream_context_create($contextOptions);

        // get the file
        $fileContent = @file_get_contents($configjsFile, false, $context);

        if ($fileContent !== false) {

            // when we need only uncommented values
            if ($raw === false) {
                // remove block comments
                $platformConfigjs = preg_replace('!/\*.*?\*/!s', '', $fileContent);
                // remove single-line comments
                $platformConfigjs = preg_replace('/\/\/[^\n]*/', '', $platformConfigjs);
                // remove empty lines
                $platformConfigjs = preg_replace('/^\s*[\r\n]/m', '', $platformConfigjs);

            // when we need the full file as it is
            } else {
                $platformConfigjs = $fileContent;
            }
        }

        return $platformConfigjs;

    }


    // loading the interface_config.js
    public function getPlatformInterfaceConfigjs($jitsiUrl, $raw = false) {
        // constructing the URL
        $interfaceConfigjsFile = $jitsiUrl . '/interface_config.js';

        // default content, if we can't get the file contents
        $platformInterfaceConfigjs = "The file $interfaceConfigjsFile can't be loaded.";

        // ssl options
        $contextOptions = [
            'ssl' => [
                'verify_peer'		=> true,
                'verify_peer_name'	=> true,
            ],
        ];
        $context = stream_context_create($contextOptions);

        // get the file
        $fileContent = @file_get_contents($interfaceConfigjsFile, false, $context);

        if ($fileContent !== false) {

            // when we need only uncommented values
            if ($raw === false) {
                // remove block comments
                $platformInterfaceConfigjs = preg_replace('!/\*.*?\*/!s', '', $fileContent);
                // remove single-line comments
                $platformInterfaceConfigjs = preg_replace('/\/\/[^\n]*/', '', $platformInterfaceConfigjs);
                // remove empty lines
                $platformInterfaceConfigjs = preg_replace('/^\s*[\r\n]/m', '', $platformInterfaceConfigjs);

            // when we need the full file as it is
            } else {
                $platformInterfaceConfigjs = $fileContent;
            }
        }

        return $platformInterfaceConfigjs;

    }


}

?>
