<?php

declare(strict_types=1);

namespace App\Publishing;

use App\Enums\BundledPostArtwork;
use App\Enums\ContentReadinessStatus;
use App\Enums\ReadinessCheck;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;

/**
 * The per-record readiness checklist and the publishing rule built on it: which
 * checks a record passes, and which required-to-publish checks are still missing.
 * ContentReadinessCriteria holds the SQL form of the same checks;
 * ContentReadinessCriteriaTest proves the two agree. Display text belongs to the
 * caller (ReadinessCheck labels, ReadinessColumn).
 *
 * @see ContentReadinessCriteria
 */
final class ContentReadiness
{
    /** @var array<string, bool>|null Whether each check passes, keyed by ReadinessCheck value. */
    private ?array $completion = null;

    public function __construct(private readonly Post|Project|Podcast|Episode|NewsletterIssue|Video $content) {}

    /**
     * The checks for the record's content type, in checklist order.
     *
     * @return list<ReadinessCheck>
     */
    public function checks(): array
    {
        return array_map(ReadinessCheck::from(...), array_keys($this->completion()));
    }

    public function isComplete(ReadinessCheck $check): bool
    {
        return $this->completion()[$check->value] ?? false;
    }

    /**
     * The checks the record still fails, in checklist order.
     *
     * @return list<ReadinessCheck>
     */
    public function missing(): array
    {
        return array_values(array_filter($this->checks(), fn (ReadinessCheck $check): bool => ! $this->isComplete($check)));
    }

    public function isReady(): bool
    {
        return $this->missing() === [];
    }

    public function status(): ContentReadinessStatus
    {
        return $this->isReady() ? ContentReadinessStatus::Ready : ContentReadinessStatus::NeedsAttention;
    }

    /**
     * The required-to-publish checks that are still incomplete. The rest of the
     * checklist is advisory and never blocks publishing.
     *
     * @return list<ReadinessCheck>
     */
    public function publishingIssues(): array
    {
        $required = match (true) {
            $this->content instanceof Post => [ReadinessCheck::Content, ReadinessCheck::Excerpt, ReadinessCheck::Category],
            $this->content instanceof Project => [ReadinessCheck::Description, ReadinessCheck::CaseStudy],
            $this->content instanceof Episode => [ReadinessCheck::Podcast, ReadinessCheck::Description, ReadinessCheck::EpisodeMedia],
            $this->content instanceof NewsletterIssue => [ReadinessCheck::Content],
            default => [],
        };

        return array_values(array_filter($required, fn (ReadinessCheck $check): bool => ! $this->isComplete($check)));
    }

    /**
     * Whether each check passes, keyed by ReadinessCheck value in checklist order.
     * Computed once per instance, so create a new instance after changing the record.
     *
     * @return array<string, bool>
     */
    private function completion(): array
    {
        return $this->completion ??= match (true) {
            $this->content instanceof Post => $this->postChecks($this->content),
            $this->content instanceof Project => $this->projectChecks($this->content),
            $this->content instanceof Podcast => $this->podcastChecks($this->content),
            $this->content instanceof Episode => $this->episodeChecks($this->content),
            $this->content instanceof NewsletterIssue => $this->newsletterIssueChecks($this->content),
            $this->content instanceof Video => $this->videoChecks($this->content),
        };
    }

    /**
     * @return array<string, bool>
     */
    private function postChecks(Post $post): array
    {
        return [
            ReadinessCheck::Content->value => filled($post->content),
            ReadinessCheck::Excerpt->value => filled($post->excerpt),
            ReadinessCheck::FeaturedImage->value => filled($post->featured_image_path) || BundledPostArtwork::tryFrom($post->slug) !== null,
            ReadinessCheck::Category->value => $post->category_id !== null,
            ReadinessCheck::Tags->value => $this->hasTags($post),
            ReadinessCheck::SeoDescription->value => $this->hasSeoDescription($post),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function projectChecks(Project $project): array
    {
        return [
            ReadinessCheck::Description->value => filled($project->description),
            ReadinessCheck::CaseStudy->value => filled($project->content),
            ReadinessCheck::FeaturedImage->value => filled($project->featured_image_path),
            ReadinessCheck::ProjectLink->value => filled($project->url) || filled($project->github_url),
            ReadinessCheck::TechStack->value => $project->technologies() !== [],
            ReadinessCheck::Tags->value => $this->hasTags($project),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function podcastChecks(Podcast $podcast): array
    {
        return [
            ReadinessCheck::Description->value => filled($podcast->description),
            ReadinessCheck::LongDescription->value => filled($podcast->long_description),
            ReadinessCheck::CoverImage->value => filled($podcast->cover_image_path),
            ReadinessCheck::SubscribeLink->value => filled($podcast->apple_url)
                || filled($podcast->spotify_url)
                || filled($podcast->rss_url)
                || filled($podcast->youtube_url),
            ReadinessCheck::SeoDescription->value => $this->hasSeoDescription($podcast),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function episodeChecks(Episode $episode): array
    {
        return [
            ReadinessCheck::Podcast->value => $episode->podcast_id !== null,
            ReadinessCheck::Description->value => filled($episode->description),
            ReadinessCheck::EpisodeMedia->value => $episode->hasMedia(),
            ReadinessCheck::ShowNotes->value => filled($episode->show_notes),
            ReadinessCheck::FeaturedImage->value => filled($episode->featured_image_path),
            ReadinessCheck::Tags->value => $this->hasTags($episode),
            ReadinessCheck::SeoDescription->value => $this->hasSeoDescription($episode),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function newsletterIssueChecks(NewsletterIssue $issue): array
    {
        return [
            ReadinessCheck::Content->value => filled($issue->content),
            ReadinessCheck::Excerpt->value => filled($issue->excerpt),
            ReadinessCheck::SeoDescription->value => $this->hasSeoDescription($issue),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function videoChecks(Video $video): array
    {
        return [
            ReadinessCheck::Title->value => filled($video->title),
            ReadinessCheck::YoutubeId->value => filled($video->youtube_id),
            ReadinessCheck::Description->value => filled($video->description),
            ReadinessCheck::Thumbnail->value => filled($video->thumbnail_url),
            ReadinessCheck::Duration->value => filled($video->duration),
            ReadinessCheck::Synced->value => $video->synced_at !== null,
        ];
    }

    private function hasTags(Post|Project|Episode $content): bool
    {
        if ($content->relationLoaded('tags')) {
            return $content->tags->isNotEmpty();
        }

        if (array_key_exists('tags_count', $content->getAttributes())) {
            $tagsCount = $content->getAttribute('tags_count');

            return is_numeric($tagsCount) && (int) $tagsCount > 0;
        }

        return $content->tags()
            ->exists();
    }

    /**
     * An SEO description saved for the record, or the column its page uses as the description
     * when none is saved (the same columns ContentReadinessCriteria checks).
     */
    private function hasSeoDescription(Post|Project|Podcast|Episode|NewsletterIssue $content): bool
    {
        $seo = $content->relationLoaded('seo') ? $content->getRelation('seo') : $content->seo;

        if ($seo instanceof Model && filled($seo->getAttribute('description'))) {
            return true;
        }

        return filled($content instanceof Post || $content instanceof NewsletterIssue
            ? $content->excerpt
            : $content->description);
    }
}
