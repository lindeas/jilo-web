<?php

/**
 * Uploads helper
 * 
 * This helper handles the file upload functionality.
 * Can be used to upload files to specified folders under public_html
 */

use App\App;

if (!function_exists('core_public_root')) {
    /**
     * Resolve the absolute path to the public_html folder regardless of context (web/CLI/tests).
     */
    function core_public_root(): string
    {
        if (defined('APP_ROOT')) {
            $projectRoot = rtrim(APP_ROOT, DIRECTORY_SEPARATOR);
        } else {
            $projectRoot = dirname(__DIR__, 2);
        }
        return $projectRoot . DIRECTORY_SEPARATOR . 'public_html';
    }
}

if (!function_exists('core_public_path')) {
    /**
     * Build an absolute path inside public_html for filesystem operations.
     */
    function core_public_path(string $relative = ''): string
    {
        $root = rtrim(core_public_root(), DIRECTORY_SEPARATOR);
        $relativePath = trim($relative);
        if ($relativePath === '') {
            return $root;
        }
        return $root . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('core_upload_relative_dir')) {
    /**
     * Resolve a config-driven upload subdirectory (always returns normalized relative path).
     */
    function core_upload_relative_dir(string $configKey, string $default): string
    {
        $config = App::config();
        $relative = trim((string)($config[$configKey] ?? $default));
        if ($relative === '') {
            $relative = $default;
        }
        return rtrim(str_replace('\\', '/', $relative), '/') . '/';
    }
}

if (!function_exists('core_upload_absolute_dir')) {
    /**
     * Convert the configured upload subdirectory into an absolute filesystem path.
     */
    function core_upload_absolute_dir(string $configKey, string $default): string
    {
        $relative = core_upload_relative_dir($configKey, $default);
        if (preg_match('#^([A-Za-z]:\\\\|/)#', $relative)) {
            return rtrim($relative, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        return rtrim(core_public_path($relative), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('core_normalize_site_url')) {
    /**
     * Build a fully-qualified URL pointing to a resource relative to the site root.
     */
    function core_normalize_site_url(string $relativePath): string
    {
        $config = App::config();
        $folder = $config['folder'] ?? '/';
        $domain = $config['domain'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

        $base = rtrim($scheme . $domain, '/') . '/' . ltrim($folder, '/');
        $base = rtrim($base, '/');

        $relative = ltrim($relativePath, '/');
        return $base . '/' . $relative;
    }
}

if (!function_exists('core_normalize_upload_files_array')) {
    /**
     * Flatten the $_FILES payload regardless of single or multiple upload inputs.
     *
     * @param array|null $fileInput
     * @return array<int,array<string,mixed>>
     */
    function core_normalize_upload_files_array(?array $fileInput): array
    {
        if (empty($fileInput)) {
            return [];
        }
        if (isset($fileInput['name']) && is_array($fileInput['name'])) {
            $normalized = [];
            foreach ($fileInput['name'] as $idx => $name) {
                $normalized[] = [
                    'name' => $name,
                    'type' => $fileInput['type'][$idx] ?? null,
                    'tmp_name' => $fileInput['tmp_name'][$idx] ?? null,
                    'error' => $fileInput['error'][$idx] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $fileInput['size'][$idx] ?? 0,
                ];
            }
            return $normalized;
        }
        return [$fileInput];
    }
}

if (!function_exists('core_store_upload_files')) {
    /**
     * Validate and persist uploaded files according to provided options.
     *
     * @param array $fileInput Raw $_FILES entry (single or multiple)
     * @param array $options   Behavior overrides: limit, config key, validation, naming, etc.
     *
     * @return array<int,string> Relative paths of stored files
     */
    function core_store_upload_files(array $fileInput, array $options): array
    {
        $defaults = [
            'limit' => 1,
            'user_id' => 0,
            'config_key' => 'uploads_path',
            'default_subdir' => 'uploads/',
            'allowed_extensions' => ['jpg', 'jpeg', 'png'],
            'allowed_mime' => ['image/jpeg', 'image/png'],
            'max_size' => 2 * 1024 * 1024,
            'name_prefix' => 'upload-',
        ];
        $options = array_merge($defaults, $options);

        $stored = [];
        $normalizedFiles = core_normalize_upload_files_array($fileInput);
        if (empty($normalizedFiles)) {
            return $stored;
        }

        // Resolve filesystem + relative directories once to avoid repeated IO operations.
        $relativeDir = core_upload_relative_dir($options['config_key'], $options['default_subdir']);
        $absoluteDir = core_upload_absolute_dir($options['config_key'], $options['default_subdir']);
        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            return $stored;
        }
        if (!is_writable($absoluteDir)) {
            return $stored;
        }

        $finfo = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;

        foreach ($normalizedFiles as $file) {
            if (count($stored) >= (int)$options['limit']) {
                break;
            }
            $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($error !== UPLOAD_ERR_OK) {
                continue;
            }
            $tmpName = (string)($file['tmp_name'] ?? '');
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                continue;
            }
            $size = (int)($file['size'] ?? 0);
            if ($size <= 0 || $size > (int)$options['max_size']) {
                continue;
            }

            $extension = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
            if (!in_array($extension, $options['allowed_extensions'], true)) {
                continue;
            }

            $mime = $finfo && $tmpName ? $finfo->file($tmpName) : null;
            if ($mime && !in_array($mime, $options['allowed_mime'], true)) {
                continue;
            }

            $unique = $options['name_prefix'] . $options['user_id'] . '-' . bin2hex(random_bytes(4)) . '-' . time();
            $fileName = $unique . '.' . $extension;
            $destPath = $absoluteDir . $fileName;

            if (!move_uploaded_file($tmpName, $destPath)) {
                continue;
            }

            $stored[] = $relativeDir . $fileName;
        }

        return $stored;
    }
}

