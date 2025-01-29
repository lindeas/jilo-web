<?php

/**
 * class Settings
 *
 * Handles editing and fetching jilo configuration.
 */
class Settings {

    /**
     * Loads javascript file the Jitsi server.
     *
     * @param string $jitsiUrl The base URL of the Jitsi server.
     * @param string $livejsFile The name of the remote js file to load.
     * @param bool $raw Whether to return the full file (true) or only uncommented values (false).
     *
     * @return string The content of the interface_config.js file or an error message.
     */
    public function getPlatformJsFile($jitsiUrl, $livejsFile, $raw = false) {
        // constructing the URL
        $jsFile = $jitsiUrl . '/' . $livejsFile;

        // default content, if we can't get the file contents
        $jsFileContent = "The file $livejsFile can't be loaded.";

        // Check if URL is valid
        if (!filter_var($jsFile, FILTER_VALIDATE_URL)) {
            return "Invalid URL: $jsFile";
        }

        // ssl options
        $contextOptions = [
            'ssl' => [
                'verify_peer'		=> true,
                'verify_peer_name'	=> true,
            ],
        ];
        $context = stream_context_create($contextOptions);

        // Try to get headers first to check if file exists and wasn't redirected
        $headers = @get_headers($jsFile, 1);  // 1 to get headers as array
        if ($headers === false) {
            return "The file $livejsFile can't be loaded (connection error).";
        }

        // Check for redirects
        $statusLine = $headers[0];
        if (strpos($statusLine, '301') !== false || strpos($statusLine, '302') !== false) {
            return "The file $livejsFile was redirected - this might indicate the file doesn't exist.";
        }

        // Check if we got 200 OK
        if (strpos($statusLine, '200') === false) {
            return "The file $livejsFile can't be loaded (HTTP error: $statusLine).";
        }

        // Check content type
        $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : '';
        if (is_array($contentType)) {
            $contentType = end($contentType); // get last content-type in case of redirects
        }
        if (stripos($contentType, 'javascript') === false && stripos($contentType, 'text/plain') === false) {
            return "The file $livejsFile doesn't appear to be a JavaScript file (got $contentType).";
        }

        // get the file
        $fileContent = @file_get_contents($jsFile, false, $context);

        if ($fileContent !== false) {
            // Quick validation of content
            $firstLine = strtolower(trim(substr($fileContent, 0, 100)));
            if (strpos($firstLine, '<!doctype html>') !== false || 
                strpos($firstLine, '<html') !== false || 
                strpos($firstLine, '<?xml') !== false) {
                return "The file $livejsFile appears to be HTML/XML content instead of JavaScript.";
            }

            // when we need only uncommented values
            if ($raw === false) {
                // remove block comments
                $jsFileContent = preg_replace('!/\*.*?\*/!s', '', $fileContent);
                // remove single-line comments
                $jsFileContent = preg_replace('/\/\/[^\n]*/', '', $jsFileContent);
                // remove empty lines
                $jsFileContent = preg_replace('/^\s*[\r\n]/m', '', $jsFileContent);

            // when we need the full file as it is
            } else {
                $jsFileContent = $fileContent;
            }
        }

        return $jsFileContent;

    }

}

?>
