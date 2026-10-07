<?php

declare(strict_types=1);

namespace App\Support\Content;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Facades\URL;

final class PreviewUrlGenerator
{
    public function for(Post|Project|Episode|NewsletterIssue $content): string
    {
        return match (true) {
            $content instanceof Post => URL::temporarySignedRoute(
                'preview.post',
                now()->addHours(2),
                ['post' => $content],
            ),
            $content instanceof Project => URL::temporarySignedRoute(
                'preview.project',
                now()->addHours(2),
                ['project' => $content],
            ),
            $content instanceof Episode => URL::temporarySignedRoute(
                'preview.episode',
                now()->addHours(2),
                ['episode' => $content],
            ),
            $content instanceof NewsletterIssue => URL::temporarySignedRoute(
                'preview.newsletter-issue',
                now()->addHours(2),
                ['newsletterIssue' => $content],
            ),
        };
    }

    /**
     * The public page for content that is live on the site, or null while the public page
     * would not be reachable (unpublished, scheduled, or an episode of an inactive show).
     */
    public function publicUrl(Post|Project|Episode|NewsletterIssue $content): ?string
    {
        if (! $content->isPublished()) {
            return null;
        }

        return match (true) {
            $content instanceof Post => route('blog.show', $content),
            $content instanceof Project => route('projects.show', $content),
            $content instanceof Episode => $this->episodeUrl($content),
            $content instanceof NewsletterIssue => route('newsletter.issue', $content),
        };
    }

    /** The public page when the content is live, otherwise a signed preview. */
    public function publicOrPreviewUrl(Post|Project|Episode|NewsletterIssue $content): string
    {
        return $this->publicUrl($content) ?? $this->for($content);
    }

    private function episodeUrl(Episode $episode): ?string
    {
        $podcast = $episode->podcast;

        if (! $podcast instanceof Podcast || ! $podcast->is_active) {
            return null;
        }

        return route('podcast.episode', [$podcast, $episode]);
    }
}
