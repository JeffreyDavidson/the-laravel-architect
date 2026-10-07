<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * What happened to one stored image during bulk optimization. Each value is
 * the key of the count it adds to in the optimization report.
 */
enum ImageOptimizationOutcome: string
{
    case Optimized = 'optimized';
    case Skipped = 'skipped';
    case Failed = 'failed';
}
