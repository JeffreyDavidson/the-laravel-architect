<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Support\DisplayTimezone;
use Filament\Forms\Components\DateTimePicker;

/**
 * Publish date field for publishable content. Filament's default timezone is the site's
 * display timezone, so the date is entered in that timezone and stored in UTC.
 */
class PublishDatePicker extends DateTimePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Publish Date')
            ->helperText(fn (): string => DisplayTimezone::label());
    }
}
