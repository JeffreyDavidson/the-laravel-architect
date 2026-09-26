<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class QuickLinksWidget extends Widget
{
    #[\Override]
    protected string $view = 'filament.widgets.quick-links-widget';

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected static ?int $sort = -6;

    /** Static links need no deferred request; render them with the dashboard. */
    #[\Override]
    protected static bool $isLazy = false;

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'items' => [
                [
                    'url' => PostResource::getUrl('create'),
                    'label' => 'Write post',
                    'description' => 'Draft a new Laravel article',
                    'color' => 'blue',
                    'icon' => Heroicon::OutlinedPencilSquare,
                ],
                [
                    'url' => EpisodeResource::getUrl('create'),
                    'label' => 'Add episode',
                    'description' => 'Prepare audio and show notes',
                    'color' => 'green',
                    'icon' => Heroicon::OutlinedMicrophone,
                ],
                [
                    'url' => ProjectResource::getUrl('create'),
                    'label' => 'Add project',
                    'description' => 'Document a case study',
                    'color' => 'pink',
                    'icon' => Heroicon::OutlinedCodeBracket,
                ],
                [
                    'url' => NewsletterIssueResource::getUrl('create'),
                    'label' => 'Write newsletter',
                    'description' => 'Prepare the next issue',
                    'color' => 'amber',
                    'icon' => Heroicon::OutlinedEnvelope,
                ],
            ],
        ];
    }
}
