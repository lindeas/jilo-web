<?php

namespace App\Core;

class Maintenance
{
    public const FLAG_PATH = __DIR__ . '/../../storage/maintenance.flag';

    public static function isEnabled(): bool
    {
        return file_exists(self::FLAG_PATH);
    }

    public static function enable(string $message = ''): bool
    {
        $dir = dirname(self::FLAG_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $content = $message !== '' ? $message : 'Site is under maintenance';
        return file_put_contents(self::FLAG_PATH, $content) !== false;
    }

    public static function disable(): bool
    {
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
        $msg = @file_get_contents(self::FLAG_PATH);
        return is_string($msg) ? trim($msg) : '';
    }
}
