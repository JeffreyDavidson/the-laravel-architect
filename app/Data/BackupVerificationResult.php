<?php

declare(strict_types=1);

namespace App\Data;

final readonly class BackupVerificationResult
{
    /**
     * @param  list<string>  $checks  Checks that passed, safe to print.
     * @param  list<string>  $failures  Reasons the archive cannot be trusted, safe to print.
     */
    public function __construct(
        public string $disk,
        public ?string $archive,
        public array $checks,
        public array $failures,
    ) {}

    public function passed(): bool
    {
        return $this->failures === [];
    }
}
