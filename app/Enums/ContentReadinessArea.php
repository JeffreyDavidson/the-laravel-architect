<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * The dashboard's "Needs finishing" rows. Each row counts the records that fail
 * at least one of its ContentReadiness checks.
 */
enum ContentReadinessArea: string implements HasDescription, HasLabel
{
    case ProjectPreviews = 'project_previews';
    case ProjectStories = 'project_stories';
    case PodcastLinks = 'podcast_links';
    case EpisodeDetails = 'episode_details';
    case PostContent = 'post_content';
    case NewsletterIssues = 'newsletter_issues';
    case VideoMetadata = 'video_metadata';

    public function getLabel(): string
    {
        return match ($this) {
            self::ProjectPreviews => 'Project previews',
            self::ProjectStories => 'Project stories',
            self::PodcastLinks => 'Podcast links',
            self::EpisodeDetails => 'Episode details',
            self::PostContent => 'Post content',
            self::NewsletterIssues => 'Newsletter issues',
            self::VideoMetadata => 'Video metadata',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ProjectPreviews => 'Add an optimized featured image to each project.',
            self::ProjectStories => 'Finish the case study for each project.',
            self::PodcastLinks => 'Add at least one place listeners can subscribe.',
            self::EpisodeDetails => 'Add a playable episode source and show notes.',
            self::PostContent => 'Add an excerpt, image, and SEO description to each post.',
            self::NewsletterIssues => 'Add an excerpt and SEO description before sending an issue.',
            self::VideoMetadata => 'Complete the description, thumbnail, duration, and sync data.',
        };
    }

    /**
     * The readiness checks this row covers.
     *
     * @return non-empty-list<ReadinessCheck>
     */
    public function checks(): array
    {
        return match ($this) {
            self::ProjectPreviews => [ReadinessCheck::FeaturedImage],
            self::ProjectStories => [ReadinessCheck::CaseStudy],
            self::PodcastLinks => [ReadinessCheck::SubscribeLink],
            self::EpisodeDetails => [ReadinessCheck::EpisodeMedia, ReadinessCheck::ShowNotes],
            self::PostContent => [ReadinessCheck::Excerpt, ReadinessCheck::FeaturedImage, ReadinessCheck::SeoDescription],
            self::NewsletterIssues => [ReadinessCheck::Excerpt, ReadinessCheck::SeoDescription],
            self::VideoMetadata => [ReadinessCheck::Description, ReadinessCheck::Thumbnail, ReadinessCheck::Duration, ReadinessCheck::Synced],
        };
    }
}
