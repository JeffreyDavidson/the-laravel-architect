<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MediaHealthStatus: string implements HasColor, HasLabel
{
    case Healthy = 'Healthy';
    case NeedsRepair = 'Needs repair';
    case ReuploadRequired = 'Re-upload required';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Healthy => 'success',
            self::NeedsRepair => 'warning',
            self::ReuploadRequired => 'danger',
        };
    }
}
