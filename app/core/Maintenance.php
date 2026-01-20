<?php

namespace App\Core;

use App\App;

class Maintenance
{
    // Keep it simple: store the flag within the app directory
    public const FLAG_PATH = __DIR__ . '/../../app/.maintenance.flag';

    public static function isEnabled(): bool
    {
        if (getenv('JILO_MAINTENANCE') === '1') {
            return true;
        }
        // Prefer DB settings if available in the current request
        $db = App::db();
        if ($db) {
            try {
                require_once __DIR__ . '/Settings.php';
                $settings = new Settings($db);
                return $settings->get('maintenance_enabled', '0') === '1';
            } catch (\Throwable $e) {
                // fall back to file flag
            }
        }
        return file_exists(self::FLAG_PATH);
    }

    public static function enable(string $message = ''): bool
    {
        $db = App::db();
        if ($db) {
            try {
                require_once __DIR__ . '/Settings.php';
                $settings = new Settings($db);
                $ok1 = $settings->set('maintenance_enabled', '1');
                $ok2 = $settings->set('maintenance_message', $message);
                return $ok1 && $ok2;
            } catch (\Throwable $e) {
                // fall back to file flag
            }
        }
        $dir = dirname(self::FLAG_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $content = $message !== '' ? $message : 'Site is under maintenance';
        return file_put_contents(self::FLAG_PATH, $content) !== false;
    }

    public static function disable(): bool
    {
        $db = App::db();
        if ($db) {
            try {
                require_once __DIR__ . '/Settings.php';
                $settings = new Settings($db);
                $ok1 = $settings->set('maintenance_enabled', '0');
                // keep last message for reference, optional to clear
                return $ok1;
            } catch (\Throwable $e) {
                // fall back to file flag
            }
        }
        if (file_exists(self::FLAG_PATH)) {
            return unlink(self::FLAG_PATH);
        }
        return true;
    }

    public static function getMessage(): string
    {
        if (!self::isEnabled()) {
            return '';
        }
        $envMsg = getenv('JILO_MAINTENANCE_MESSAGE');
        if ($envMsg) {
            return trim($envMsg);
        }
        $db = App::db();
        if ($db) {
            try {
                require_once __DIR__ . '/Settings.php';
                $settings = new Settings($db);
                return (string)$settings->get('maintenance_message', '');
            } catch (\Throwable $e) {
                // ignore and fall back to file flag
            }
        }
        $msg = @file_get_contents(self::FLAG_PATH);
        return is_string($msg) ? trim($msg) : '';
    }
}
