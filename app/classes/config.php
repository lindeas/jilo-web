<?php

class Config {

    public function getPlatformDetails($config, $platform_id) {
        $platformDetails = $config['platforms'][$platform_id];
        return $platformDetails;
    }

    // loading the config.js
    public function getPlatformConfigjs($platformDetails) {
        // constructing the URL
        $configjsFile = $platformDetails['jitsi_url'] . '/config.js';

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
            // remove block comments
            $platformConfigjs = preg_replace('!/\*.*?\*/!s', '', $fileContent);
            // remove single-line comments
            $platformConfigjs = preg_replace('/\/\/[^\n]*/', '', $platformConfigjs);
            // remove empty lines
            $platformConfigjs = preg_replace('/^\s*[\r\n]/m', '', $platformConfigjs);
        }

        return $platformConfigjs;

    }


    // loading the interface_config.js
    public function getPlatformInterfaceConfigjs($platformDetails) {
        // constructing the URL
        $interfaceConfigjsFile = $platformDetails['jitsi_url'] . '/interface_config.js';

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
            // remove block comments
            $platformInterfaceConfigjs = preg_replace('!/\*.*?\*/!s', '', $fileContent);
            // remove single-line comments
            $platformInterfaceConfigjs = preg_replace('/\/\/[^\n]*/', '', $platformInterfaceConfigjs);
            // remove empty lines
            $platformInterfaceConfigjs = preg_replace('/^\s*[\r\n]/m', '', $platformInterfaceConfigjs);
        }

        return $platformInterfaceConfigjs;

    }


}

?>
