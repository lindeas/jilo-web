<?php

/**
 * class Config
 *
 * Handles editing and fetching of the config files.
 */
class Config {

    /**
     * Edits a config file by updating specified options.
     *
     * @param array $updatedConfig Key-value pairs of config options to update.
     * @param string $config_file Path to the config file.
     *
     * @return array Returns an array with 'success' and 'updated' keys on success, or 'success' and 'error' keys on failure.
     */
    public function editConfigFile($updatedConfig, $config_file) {
        global $logObject, $user_id;
        $allLogs = [];
        $updated = [];

        try {
            if (!is_array($updatedConfig)) {
                throw new Exception("Invalid config data: expected array");
            }

            if (!file_exists($config_file) || !is_writable($config_file)) {
                throw new Exception("Config file does not exist or is not writable: $config_file");
            }

            // First we get a fresh config file contents as text
            $config_contents = file_get_contents($config_file);
            if ($config_contents === false) {
                throw new Exception("Failed to read the config file: $config_file");
            }

            $lines = explode("\n", $config_contents);

            // We loop through the variables and update them
            foreach ($updatedConfig as $key => $newValue) {
                if (strpos($key, '[') !== false) {
                    preg_match_all('/([^\[\]]+)/', $key, $matches);
                    if (empty($matches[1])) continue;

                    $parts = $matches[1];
                    $currentPath = [];
                    $found = false;
                    $inTargetArray = false;

                    foreach ($lines as $i => $line) {
                        $line = rtrim($line);

                        if (preg_match("/^\\s*\\]/", $line)) {
                            if (!empty($currentPath)) {
                                if ($inTargetArray && end($currentPath) === $parts[0]) {
                                    $inTargetArray = false;
                                }
                                array_pop($currentPath);
                            }
                            continue;
                        }

                        if (preg_match("/^\\s*['\"]([^'\"]+)['\"]\\s*=>/", $line, $matches)) {
                            $key = $matches[1];

                            if (strpos($line, '[') !== false) {
                                $currentPath[] = $key;
                                if ($key === $parts[0]) {
                                    $inTargetArray = true;
                                }
                            } else if ($key === end($parts) && $inTargetArray) {
                                $pathMatches = true;
                                $expectedPath = array_slice($parts, 0, -1);

                                if (count($currentPath) === count($expectedPath)) {
                                    for ($j = 0; $j < count($expectedPath); $j++) {
                                        if ($currentPath[$j] !== $expectedPath[$j]) {
                                            $pathMatches = false;
                                            break;
                                        }
                                    }

                                    if ($pathMatches) {
                                        if ($newValue === 'true' || $newValue === '1') {
                                            $replacementValue = 'true';
                                        } elseif ($newValue === 'false' || $newValue === '0') {
                                            $replacementValue = 'false';
                                        } else {
                                            $replacementValue = var_export($newValue, true);
                                        }

                                        if (preg_match("/^(\\s*['\"]" . preg_quote($key, '/') . "['\"]\\s*=>\\s*).*?(,?)\\s*$/", $line, $matches)) {
                                            $lines[$i] = $matches[1] . $replacementValue . $matches[2];
                                            $updated[] = implode('.', array_merge($currentPath, [$key]));
                                            $found = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!$found) {
                        $allLogs[] = "Failed to update: $key";
                    }
                } else {
                    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                        throw new Exception("Invalid config key format: $key");
                    }

                    if ($newValue === 'true' || $newValue === '1') {
                        $replacementValue = 'true';
                    } elseif ($newValue === 'false' || $newValue === '0') {
                        $replacementValue = 'false';
                    } else {
                        $replacementValue = var_export($newValue, true);
                    }

                    $found = false;
                    foreach ($lines as $i => $line) {
                        if (preg_match("/^(\\s*['\"]" . preg_quote($key, '/') . "['\"]\\s*=>\\s*).*?(,?)\\s*$/", $line, $matches)) {
                            $lines[$i] = $matches[1] . $replacementValue . $matches[2];
                            $updated[] = $key;
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $allLogs[] = "Failed to update: $key";
                    }
                }
            }

            // We write the new config file
            $new_contents = implode("\n", $lines);
            if (file_put_contents($config_file, $new_contents) === false) {
                throw new Exception("Failed to write the config file: $config_file");
            }

            if (!empty($allLogs)) {
                $logObject->insertLog($user_id, implode("\n", $allLogs), 'system');
            }

            return [
                'success' => true,
                'updated' => $updated
            ];
        } catch (Exception $e) {
            $logObject->insertLog($user_id, "Config update error: " . $e->getMessage(), 'system');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
