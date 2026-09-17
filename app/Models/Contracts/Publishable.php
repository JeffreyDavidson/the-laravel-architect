<?php

declare(strict_types=1);

namespace App\Models\Contracts;

interface Publishable
{
    public function isPublished(): bool;
}
