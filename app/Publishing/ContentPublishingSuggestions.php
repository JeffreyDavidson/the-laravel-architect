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
use JeffreyDavidson\CreatorKit\Contracts\PublishingSuggestions;

/**
 * TLA's answer to creator-kit's "what could still be improved?": the labels of the checks
 * `ContentReadiness` reports missing that do not block publishing (featured image, tags, SEO
 * description and the like). Bound in `AppServiceProvider`; the package's readiness column
 * shows them under a "Ready" badge. Suggestions never gate anything, so content TLA has no
 * readiness rules for simply has none.
 */
final class ContentPublishingSuggestions implements PublishingSuggestions
{
    public function suggestions(Model&Publishable $content): array
    {
        if (! $content instanceof Post && ! $content instanceof Project && ! $content instanceof Episode && ! $content instanceof NewsletterIssue) {
            return [];
        }

        $readiness = new ContentReadiness($content);
        $blocking = $readiness->publishingIssues();

        return array_values(array_map(
            static fn (ReadinessCheck $check): string => $check->getLabel(),
            array_filter($readiness->missing(), static fn (ReadinessCheck $check): bool => ! in_array($check, $blocking, true)),
        ));
    }
}
