<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Presenters\EpisodePresenter;
use App\Presenters\NewsletterIssuePresenter;
use App\Presenters\PostPresenter;
use App\Presenters\ProjectPresenter;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\ContentUrls as ContentUrlsContract;
use LogicException;

/**
 * Where creator-kit's "View on site" button sends an editor: each content type's presenter
 * builds the public page, or a signed preview until it is live. Bound to the package's
 * `ContentUrls` in `AppServiceProvider`.
 */
final class ContentUrls implements ContentUrlsContract
{
    public function publicOrPreviewUrl(Model $record): string
    {
        $presenter = match (true) {
            $record instanceof Post => PostPresenter::from($record),
            $record instanceof Project => ProjectPresenter::from($record),
            $record instanceof Episode => EpisodePresenter::from($record),
            $record instanceof NewsletterIssue => NewsletterIssuePresenter::from($record),
            default => throw new LogicException("No public page for {$record->getMorphClass()}."),
        };

        return $presenter->publicOrPreviewUrl();
    }
}
