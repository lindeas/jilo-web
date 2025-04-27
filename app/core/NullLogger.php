<?php

namespace App\Core;

/**
 * NullLogger is a fallback for disabling logging when there is no logging plugin enabled.
 */
class NullLogger
{
    /**
     * PSR-3 compatible log stub.
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log(string $level, string $message, array $context = []): void {}
}
