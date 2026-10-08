<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Presenters\EpisodePresenter;
use App\Presenters\NewsletterIssuePresenter;
use App\Presenters\PostPresenter;
use App\Presenters\ProjectPresenter;
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
            ->url(fn (Post|Project|Episode|NewsletterIssue $record): string => $this->publicOrPreviewUrl($record))
            ->openUrlInNewTab();
    }

    private function publicOrPreviewUrl(Post|Project|Episode|NewsletterIssue $record): string
    {
        $presenter = match (true) {
            $record instanceof Post => PostPresenter::from($record),
            $record instanceof Project => ProjectPresenter::from($record),
            $record instanceof Episode => EpisodePresenter::from($record),
            $record instanceof NewsletterIssue => NewsletterIssuePresenter::from($record),
        };

        return $presenter->publicOrPreviewUrl();
    }
}
