<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MediaHealthStatus: string implements HasColor, HasLabel
{
    case Healthy = 'healthy';
    case NeedsRepair = 'needs_repair';
    case ReuploadRequired = 'reupload_required';

    public function getLabel(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::NeedsRepair => 'Needs repair',
            self::ReuploadRequired => 'Re-upload required',
        };
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
