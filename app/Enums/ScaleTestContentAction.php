<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The actions accepted by the content:scale-test command.
 */
enum ScaleTestContentAction: string
{
    case Seed = 'seed';
    case Clear = 'clear';
}
