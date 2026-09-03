<?php

namespace App\Services;

use Illuminate\Database\DatabaseManager;
use RuntimeException;
use Throwable;

class DatabaseHealthMonitor
{
    public function __construct(private readonly DatabaseManager $database) {}

    public function ensureHealthy(): void
    {
        $connection = $this->database->connection();

        if ($connection->getDriverName() !== 'sqlite') {
            return;
        }

        try {
            $integrity = $this->pragmaValue($connection->selectOne('PRAGMA quick_check'));
            $busyTimeout = $this->pragmaValue($connection->selectOne('PRAGMA busy_timeout'));
            $journalMode = $this->pragmaValue($connection->selectOne('PRAGMA journal_mode'));
            $synchronous = $this->pragmaValue($connection->selectOne('PRAGMA synchronous'));
        } catch (Throwable $exception) {
            throw new RuntimeException('The SQLite database could not be inspected.', previous: $exception);
        }

        if ($integrity !== 'ok') {
            throw new RuntimeException('The SQLite integrity check failed.');
        }

        if (! is_int($busyTimeout)
            || $busyTimeout < 5000
            || ! is_string($journalMode)
            || strtolower($journalMode) !== 'wal'
            || ! in_array($synchronous, [1, 2], true)) {
            throw new RuntimeException('The SQLite concurrency safeguards are not active.');
        }
    }

    private function pragmaValue(mixed $result): mixed
    {
        if (! is_object($result)) {
            return null;
        }

        return array_values((array) $result)[0] ?? null;
    }
}
