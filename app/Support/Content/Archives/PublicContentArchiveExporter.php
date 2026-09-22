<?php

declare(strict_types=1);

namespace App\Support\Content\Archives;

use App\Models\Category;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Spatie\Tags\Tag;

/**
 * @phpstan-type ContentRecord array<string, mixed>
 * @phpstan-type ContentRecords list<ContentRecord>
 */
class PublicContentArchiveExporter
{
    private const int VERSION = 1;

    private const array POST_FIELDS = ['title', 'slug', 'excerpt', 'content', 'featured_image_path', 'published_at'];

    private const array PROJECT_FIELDS = ['title', 'slug', 'description', 'content', 'featured_image_path', 'url', 'github_url', 'tech_stack', 'is_featured', 'sort_order'];

    private const array PODCAST_FIELDS = ['name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'sort_order'];

    private const array EPISODE_FIELDS = ['title', 'slug', 'episode_number', 'season_number', 'description', 'show_notes', 'transcript', 'featured_image_path', 'audio_url', 'audio_path', 'embed_url', 'youtube_url', 'duration_minutes', 'guest_name', 'guest_title', 'guest_url', 'published_at'];

    private const array NEWSLETTER_ISSUE_FIELDS = ['title', 'slug', 'excerpt', 'content', 'published_at'];

    private const array VIDEO_FIELDS = ['youtube_id', 'title', 'slug', 'description', 'thumbnail_url', 'duration', 'view_count', 'like_count', 'comment_count', 'is_featured', 'published_at', 'synced_at'];

    private const array SEO_FIELDS = ['description', 'title', 'image', 'author', 'robots', 'canonical_url'];

    /** @return array<string, mixed> */
    public function export(): array
    {
        $posts = Post::query()
            ->published()
            ->with(['category', 'tags', 'seo'])
            ->orderBy('published_at')
            ->orderBy('id')
            ->lazy(100)
            ->map(fn (Post $post): array => [
                ...$this->attributes($post, self::POST_FIELDS),
                'category_slug' => $post->category?->slug,
                'tags' => $this->tags($post),
                'seo' => $this->seo($post),
            ])
            ->values()
            ->all();

        $projects = Project::query()
            ->published()
            ->with(['tags', 'seo'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lazy(100)
            ->map(fn (Project $project): array => [
                ...$this->attributes($project, self::PROJECT_FIELDS),
                'github_url' => null,
                'tech_stack' => $project->tech_stack,
                'tags' => $this->tags($project),
                'seo' => $this->seo($project),
            ])
            ->values()
            ->all();

        $podcasts = Podcast::query()
            ->active()
            ->with('seo')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lazy(100)
            ->map(fn (Podcast $podcast): array => [
                ...$this->attributes($podcast, self::PODCAST_FIELDS),
                'seo' => $this->seo($podcast),
            ])
            ->values()
            ->all();

        $episodes = Episode::query()
            ->published()
            ->whereHas('podcast', fn (Builder $query): Builder => $query->active())
            ->with(['podcast', 'tags', 'seo'])
            ->orderBy('published_at')
            ->orderBy('id')
            ->lazy(100)
            ->map(fn (Episode $episode): array => [
                ...$this->attributes($episode, self::EPISODE_FIELDS),
                'podcast_slug' => $episode->podcast?->slug,
                'tags' => $this->tags($episode),
                'seo' => $this->seo($episode),
            ])
            ->values()
            ->all();

        $newsletterIssues = NewsletterIssue::query()
            ->published()
            ->with('seo')
            ->orderBy('published_at')
            ->orderBy('id')
            ->lazy(100)
            ->map(fn (NewsletterIssue $issue): array => [
                ...$this->attributes($issue, self::NEWSLETTER_ISSUE_FIELDS),
                'seo' => $this->seo($issue),
            ])
            ->values()
            ->all();

        return [
            'version' => self::VERSION,
            'exported_at' => now()->toAtomString(),
            'categories' => Category::query()
                ->whereHas('publishedPosts')
                ->orderBy('name')
                ->orderBy('id')
                ->select(['name', 'slug', 'description'])
                ->lazy(100)
                ->map(fn (Category $category): array => $this->attributes($category, ['name', 'slug', 'description']))
                ->values()
                ->all(),
            'posts' => $posts,
            'projects' => $projects,
            'podcasts' => $podcasts,
            'episodes' => $episodes,
            'newsletter_issues' => $newsletterIssues,
            'videos' => Video::query()
                ->published()
                ->orderBy('published_at')
                ->orderBy('id')
                ->select(self::VIDEO_FIELDS)
                ->lazy(100)
                ->map(fn (Video $video): array => $this->attributes($video, self::VIDEO_FIELDS))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function attributes(Model $model, array $fields): array
    {
        return $this->only($model->getAttributes(), $fields);
    }

    /** @param array<string, mixed> $attributes
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function only(array $attributes, array $fields): array
    {
        $selected = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $attributes)) {
                $selected[$field] = $attributes[$field];
            }
        }

        return $selected;
    }

    /** @return list<array{name: string, type: string|null}> */
    private function tags(Post|Project|Episode $model): array
    {
        $tags = [];

        foreach ($model->tags as $tag) {
            if (! $tag instanceof Tag || ! is_string($tag->name)) {
                throw new InvalidArgumentException('Public content contains an invalid tag.');
            }

            $tags[] = ['name' => $tag->name, 'type' => is_string($tag->type) ? $tag->type : null];
        }

        return $tags;
    }

    /** @return array<string, mixed>|null */
    private function seo(Post|Project|Podcast|Episode|NewsletterIssue $model): ?array
    {
        $seo = $model->seo;

        return $seo !== null && $seo->exists ? $this->attributes($seo, self::SEO_FIELDS) : null;
    }
}
