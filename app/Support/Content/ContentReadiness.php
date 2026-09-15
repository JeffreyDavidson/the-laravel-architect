<?php

namespace App\Support\Content;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;

final readonly class ContentReadiness
{
    public function __construct(private Post|Project|Podcast|Episode|NewsletterIssue|Video $content) {}

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    public function checks(): array
    {
        return match (true) {
            $this->content instanceof Post => $this->postChecks($this->content),
            $this->content instanceof Project => $this->projectChecks($this->content),
            $this->content instanceof Podcast => $this->podcastChecks($this->content),
            $this->content instanceof Episode => $this->episodeChecks($this->content),
            $this->content instanceof NewsletterIssue => $this->newsletterIssueChecks($this->content),
            $this->content instanceof Video => $this->videoChecks($this->content),
        };
    }

    public function isReady(): bool
    {
        return array_all($this->checks(), fn (array $check): bool => $check['complete']);
    }

    public function label(): string
    {
        return $this->isReady() ? 'Ready' : 'Needs attention';
    }

    public function progress(): string
    {
        $checks = $this->checks();
        $complete = count(array_filter($checks, fn (array $check): bool => $check['complete']));

        return $complete.'/'.count($checks).' complete';
    }

    public function missingSummary(): string
    {
        $missing = array_map(
            fn (array $check): string => $check['label'],
            array_filter($this->checks(), fn (array $check): bool => ! $check['complete']),
        );

        return $missing === [] ? 'All public details are complete.' : 'Missing: '.implode(', ', $missing);
    }

    public function checkComplete(string $key): bool
    {
        return $this->checks()[$key]['complete'] ?? false;
    }

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    private function postChecks(Post $post): array
    {
        return [
            'content' => [
                'label' => 'Content',
                'complete' => filled($post->content),
            ],
            'excerpt' => [
                'label' => 'Excerpt',
                'complete' => filled($post->excerpt),
            ],
            'featured_image' => [
                'label' => 'Featured image',
                'complete' => filled($post->featured_image_path),
            ],
            'category' => [
                'label' => 'Category',
                'complete' => $post->category_id !== null,
            ],
            'tags' => [
                'label' => 'Tags',
                'complete' => $this->hasTags($post),
            ],
            'seo_description' => [
                'label' => 'SEO description',
                'complete' => $this->hasSeoDescription($post),
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    private function projectChecks(Project $project): array
    {
        return [
            'description' => [
                'label' => 'Description',
                'complete' => filled($project->description),
            ],
            'case_study' => [
                'label' => 'Case study',
                'complete' => filled($project->content),
            ],
            'featured_image' => [
                'label' => 'Featured image',
                'complete' => filled($project->featured_image_path),
            ],
            'project_link' => [
                'label' => 'Project link',
                'complete' => filled($project->url) || filled($project->github_url),
            ],
            'tech_stack' => [
                'label' => 'Tech stack',
                'complete' => $this->hasTechStack($project),
            ],
            'tags' => [
                'label' => 'Tags',
                'complete' => $this->hasTags($project),
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    private function podcastChecks(Podcast $podcast): array
    {
        return [
            'description' => [
                'label' => 'Description',
                'complete' => filled($podcast->description),
            ],
            'long_description' => [
                'label' => 'About section',
                'complete' => filled($podcast->long_description),
            ],
            'cover_image' => [
                'label' => 'Cover image',
                'complete' => filled($podcast->cover_image_path),
            ],
            'subscribe_link' => [
                'label' => 'Subscribe link',
                'complete' => filled($podcast->apple_url)
                    || filled($podcast->spotify_url)
                    || filled($podcast->rss_url)
                    || filled($podcast->youtube_url),
            ],
            'seo_description' => [
                'label' => 'SEO description',
                'complete' => $this->hasSeoDescription($podcast),
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    private function episodeChecks(Episode $episode): array
    {
        return [
            'podcast' => [
                'label' => 'Podcast',
                'complete' => $episode->podcast_id !== null,
            ],
            'description' => [
                'label' => 'Description',
                'complete' => filled($episode->description),
            ],
            'episode_media' => [
                'label' => 'Episode media',
                'complete' => filled($episode->audio_url)
                    || filled($episode->audio_path)
                    || filled($episode->embed_url)
                    || filled($episode->youtube_url),
            ],
            'show_notes' => [
                'label' => 'Show notes',
                'complete' => filled($episode->show_notes),
            ],
            'featured_image' => [
                'label' => 'Featured image',
                'complete' => filled($episode->featured_image_path),
            ],
            'tags' => [
                'label' => 'Tags',
                'complete' => $this->hasTags($episode),
            ],
            'seo_description' => [
                'label' => 'SEO description',
                'complete' => $this->hasSeoDescription($episode),
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    private function newsletterIssueChecks(NewsletterIssue $issue): array
    {
        return [
            'content' => [
                'label' => 'Content',
                'complete' => filled($issue->content),
            ],
            'excerpt' => [
                'label' => 'Excerpt',
                'complete' => filled($issue->excerpt),
            ],
            'seo_description' => [
                'label' => 'SEO description',
                'complete' => $this->hasSeoDescription($issue),
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    private function videoChecks(Video $video): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'complete' => filled($video->title),
            ],
            'youtube_id' => [
                'label' => 'YouTube video',
                'complete' => filled($video->youtube_id),
            ],
            'description' => [
                'label' => 'Description',
                'complete' => filled($video->description),
            ],
            'thumbnail' => [
                'label' => 'Thumbnail',
                'complete' => filled($video->thumbnail_url),
            ],
            'duration' => [
                'label' => 'Duration',
                'complete' => filled($video->duration),
            ],
            'synced' => [
                'label' => 'YouTube sync',
                'complete' => $video->synced_at !== null,
            ],
        ];
    }

    private function hasTechStack(Project $project): bool
    {
        $techStack = $project->tech_stack;

        return is_array($techStack) && count(array_filter($techStack, fn (mixed $item): bool => is_string($item) && filled($item))) > 0;
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

        return $content->tags()->exists();
    }

    private function hasSeoDescription(Post|Project|Podcast|Episode|NewsletterIssue $content): bool
    {
        $seo = $content->relationLoaded('seo') ? $content->getRelation('seo') : $content->seo;

        if ($seo instanceof Model && filled($seo->getAttribute('description'))) {
            return true;
        }

        $seoData = $content->getDynamicSEOData();

        return filled($seoData->description);
    }
}
