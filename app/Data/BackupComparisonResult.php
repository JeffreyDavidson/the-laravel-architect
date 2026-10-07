<?php

declare(strict_types=1);

namespace App\Data;

/**
 * The outcome of comparing one part of a restored backup with the live
 * application. Messages are safe to print: they contain only counts, table
 * names, and pass/fail reasons.
 */
final readonly class BackupComparisonResult
{
    /**
     * @param  list<string>  $checks  Checks that passed.
     * @param  list<string>  $failures  Reasons the restored copy differs from the live one.
     */
    public function __construct(
        public array $checks = [],
        public array $failures = [],
    ) {}

    /** Combine results in order, keeping every check and failure. */
    public static function combine(self ...$results): self
    {
        $checks = [];
        $failures = [];

        foreach ($results as $result) {
            $checks = [...$checks, ...$result->checks];
            $failures = [...$failures, ...$result->failures];
        }

        return new self($checks, $failures);
    }
}
