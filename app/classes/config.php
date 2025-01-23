<?php

/**
 * class Config
 *
 * Handles editing and fetching ot the config files.
 */
class Config {

    /**
     * Edits a config file by updating specified options.
     *
     * @param array $updatedConfig Key-value pairs of config options to update.
     * @param string $config_file Path to the config file.
     *
     * @return mixed Returns true on success, or an error message on failure.
     */
    public function editConfigFile($updatedConfig, $config_file) {
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
            $pattern = "/(['\"]{$key}['\"]\s*=>\s*)([^,]+),/";

            // prepare the value, make booleans w/out single quotes
            if ($newValue === 'true') {
                $replacementValue = 'true';
            } elseif ($newValue === 'false') {
                $replacementValue = 'false';
            } else {
                $replacementValue = var_export($newValue, true);
            }

            // value replacing
            $config_contents = preg_replace($pattern, "$1{$replacementValue},", $config_contents);
        }

        // write the new config file
        if (!file_put_contents($config_file, $config_contents)) {
            return "Failed to write the config file \"$config_file\".";
        }

        return true;
    }

}

?>
