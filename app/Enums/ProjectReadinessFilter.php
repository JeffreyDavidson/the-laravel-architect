<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectReadinessFilter: string implements HasLabel
{
    case Ready = 'ready';
    case NeedsImage = 'needs_image';
    case NeedsCaseStudy = 'needs_case_study';
    case NeedsDetails = 'needs_details';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ready => 'Ready',
            self::NeedsImage => 'Needs image',
            self::NeedsCaseStudy => 'Needs case study',
            self::NeedsDetails => 'Needs project details',
        };
    }
}
