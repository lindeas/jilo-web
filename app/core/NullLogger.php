<?php

namespace App\Core;

/**
 * NullLogger is a fallback for disabling logging when there is no logging plugin enabled.
 */
class NullLogger
{
    /**
     * No-op insertLog.
     *
     * @param mixed $userId
     * @param string $message
     * @param string|null $type
     * @return void
     */
    public function insertLog($userId, string $message, ?string $type = null): void {}
}
