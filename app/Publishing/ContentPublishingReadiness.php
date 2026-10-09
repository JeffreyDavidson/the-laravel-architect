<?php

declare(strict_types=1);

namespace App\Publishing;

use App\Enums\ReadinessCheck;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\Publishable;
use JeffreyDavidson\CreatorKit\Contracts\PublishingReadiness;
use LogicException;

/**
 * TLA's answer to creator-kit's "what's missing before publishing?": the labels of the
 * required checks `ContentReadiness` still fails. Bound in `AppServiceProvider`, so the
 * package's `PublishContent` refuses unready content without the models knowing about
 * `app/Publishing`. Content TLA has no readiness rules for is refused rather than published
 * unchecked.
 */
final class ContentPublishingReadiness implements PublishingReadiness
{
    public function issues(Model&Publishable $content): array
    {
        if (! $content instanceof Post && ! $content instanceof Project && ! $content instanceof Episode && ! $content instanceof NewsletterIssue) {
            $type = $content::class;

            throw new LogicException("No publishing readiness rules for {$type}.");
        }

        return array_map(
            static fn (ReadinessCheck $check): string => $check->getLabel(),
            new ContentReadiness($content)->publishingIssues(),
        );
    }
}
