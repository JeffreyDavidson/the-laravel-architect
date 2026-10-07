<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Opens the content in a new tab: the public page while it is live, otherwise a signed preview.
 */
final class ViewOnSiteAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'view_on_site';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('View on site')
            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
            ->url(fn (Post|Project|Episode|NewsletterIssue $record, PreviewUrlGenerator $previewUrlGenerator): string => $previewUrlGenerator->publicOrPreviewUrl($record))
            ->openUrlInNewTab();
    }
}
