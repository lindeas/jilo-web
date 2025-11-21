<?php

namespace App\Core;

use Exception;

class MigrationException extends Exception
{
    private string $migration;

    public function __construct(string $migration, string $message, ?Exception $previous = null)
    {
        $this->migration = $migration;
        parent::__construct($message, 0, $previous);
    }

    public function getMigration(): string
    {
        return $this->migration;
    }
}
