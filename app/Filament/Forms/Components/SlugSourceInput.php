<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * The field a slug is derived from, such as a title or name. On create it fills a blank
 * `slug` field when the editor leaves this field; an existing or hand-written slug is kept.
 */
final class SlugSourceInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->live(onBlur: true)
            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                if ($operation === 'create' && blank($get('slug'))) {
                    $set('slug', Str::slug($state ?? ''));
                }
            });
    }
}
