<?php

/**
 * Datetime helper utilities.
 *
 * Centralized formatting for UTC timestamps into a specified timezone.
 */

if (!function_exists('app_format_local_datetime')) {
    function app_format_local_datetime(?string $value, string $format, string $timezone): string
    {
        if (empty($value) || $value === '0000-00-00 00:00:00') {
            return '';
        }
        try {
            $utc = new DateTimeZone('UTC');
            $tz = new DateTimeZone($timezone ?: 'UTC');
            $date = new DateTimeImmutable($value, $utc);
            return $date->setTimezone($tz)->format($format);
        } catch (Throwable $e) {
            return '';
        }
    }
}
