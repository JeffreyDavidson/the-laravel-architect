<?php

namespace App\Support\Content\Archives;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Tags\Tag;

/**
 * @phpstan-type ContentRecord array<string, mixed>
 * @phpstan-type ContentRecords list<ContentRecord>
 * @phpstan-type ArchiveRecords array{categories: ContentRecords, posts: ContentRecords, projects: ContentRecords, podcasts: ContentRecords, episodes: ContentRecords, videos: ContentRecords}
 */
class PublicContentArchive
{
    private const int VERSION = 1;

    public function __construct(
        private readonly PublicContentArchiveValidator $validator,
        private readonly PublicContentArchiveMedia $media,
        private readonly PublicContentArchiveRelations $relations,
    ) {}

    private const array POST_FIELDS = ['title', 'slug', 'excerpt', 'content', 'featured_image_path', 'published_at'];

    private const array PROJECT_FIELDS = ['title', 'slug', 'description', 'content', 'featured_image_path', 'url', 'github_url', 'tech_stack', 'is_featured', 'sort_order'];

    private const array PODCAST_FIELDS = ['name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'sort_order'];

    private const array EPISODE_FIELDS = ['title', 'slug', 'episode_number', 'season_number', 'description', 'show_notes', 'featured_image_path', 'audio_url', 'audio_path', 'embed_url', 'youtube_url', 'duration_minutes', 'guest_name', 'guest_title', 'guest_url', 'published_at'];

    private const array VIDEO_FIELDS = ['youtube_id', 'title', 'slug', 'description', 'thumbnail_url', 'duration', 'view_count', 'like_count', 'comment_count', 'is_featured', 'published_at', 'synced_at'];

    private const array SEO_FIELDS = ['description', 'title', 'image', 'author', 'robots', 'canonical_url'];

    /** @return array<string, mixed> */
    public function export(): array
    {
        $posts = Post::query()
            ->published()
            ->with(['category', 'tags', 'seo'])
            ->orderBy('published_at')
            ->get()
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
            ->get()
            ->map(fn (Project $project): array => [
                ...$this->attributes($project, self::PROJECT_FIELDS),
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
            ->get()
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
            ->get()
            ->map(fn (Episode $episode): array => [
                ...$this->attributes($episode, self::EPISODE_FIELDS),
                'podcast_slug' => $episode->podcast?->slug,
                'tags' => $this->tags($episode),
                'seo' => $this->seo($episode),
            ])
            ->values()
            ->all();

        return [
            'version' => self::VERSION,
            'exported_at' => now()->toAtomString(),
            'categories' => Category::query()
                ->whereHas('publishedPosts')
                ->orderBy('name')
                ->get(['name', 'slug', 'description'])
                ->map(fn (Category $category): array => $this->attributes($category, ['name', 'slug', 'description']))
                ->values()
                ->all(),
            'posts' => $posts,
            'projects' => $projects,
            'podcasts' => $podcasts,
            'episodes' => $episodes,
            'videos' => Video::query()
                ->published()
                ->orderBy('published_at')
                ->get(self::VIDEO_FIELDS)
                ->map(fn (Video $video): array => $this->attributes($video, self::VIDEO_FIELDS))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return array<string, int>
     */
    public function sync(array $archive): array
    {
        $records = $this->validator->validate($archive, self::VERSION);

        return Model::withoutEvents(fn (): array => DB::transaction(function () use ($records): array {
            $this->unpublishExistingContent();
            $author = $this->stagingAuthor();

            foreach ($records['categories'] as $attributes) {
                Category::query()->updateOrCreate(
                    ['slug' => $this->stringValue($attributes, 'slug')],
                    $this->only($attributes, ['name', 'description']),
                );
            }

            foreach ($records['podcasts'] as $attributes) {
                $podcast = Podcast::query()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
                $podcast->fill([...$this->only($attributes, self::PODCAST_FIELDS), 'is_active' => true]);
                $podcast->save();
                $this->relations->syncSeo($podcast, $this->nullableRecord($attributes['seo'] ?? null, 'podcast SEO'), self::SEO_FIELDS);
            }

            foreach ($records['posts'] as $attributes) {
                $post = Post::query()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
                $post->fill([
                    ...$this->only($attributes, self::POST_FIELDS),
                    'category_id' => Category::query()->where('slug', $this->nullableStringValue($attributes, 'category_slug'))->value('id'),
                    'user_id' => $author->getKey(),
                    'status' => PublishStatus::Published,
                    'review_notes' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);
                $post->save();
                $this->relations->syncTags($post, $this->relations->tagRecords($attributes['tags'] ?? []));
                $this->relations->syncSeo($post, $this->nullableRecord($attributes['seo'] ?? null, 'post SEO'), self::SEO_FIELDS);
            }

            foreach ($records['projects'] as $attributes) {
                $project = Project::query()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
                $project->fill([...$this->only($attributes, self::PROJECT_FIELDS), 'status' => PublishStatus::Published]);
                $project->save();
                $this->relations->syncTags($project, $this->relations->tagRecords($attributes['tags'] ?? []));
                $this->relations->syncSeo($project, $this->nullableRecord($attributes['seo'] ?? null, 'project SEO'), self::SEO_FIELDS);
            }

            foreach ($records['episodes'] as $attributes) {
                $episode = Episode::query()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
                $episode->fill([
                    ...$this->only($attributes, self::EPISODE_FIELDS),
                    'podcast_id' => Podcast::query()->where('slug', $this->nullableStringValue($attributes, 'podcast_slug'))->value('id'),
                    'status' => PublishStatus::Published,
                ]);
                $episode->save();
                $this->relations->syncTags($episode, $this->relations->tagRecords($attributes['tags'] ?? []));
                $this->relations->syncSeo($episode, $this->nullableRecord($attributes['seo'] ?? null, 'episode SEO'), self::SEO_FIELDS);
            }

            foreach ($records['videos'] as $attributes) {
                Video::query()->updateOrCreate(
                    ['youtube_id' => $this->stringValue($attributes, 'youtube_id')],
                    $this->only($attributes, self::VIDEO_FIELDS),
                );
            }

            return collect(['categories', 'posts', 'projects', 'podcasts', 'episodes', 'videos'])
                ->mapWithKeys(fn (string $type): array => [$type => count($records[$type])])
                ->all();
        }));
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return list<string>
     */
    public function mediaPaths(array $archive): array
    {
        $records = $this->validator->validate($archive, self::VERSION);

        $paths = [];

        foreach (['posts', 'projects', 'podcasts', 'episodes'] as $type) {
            foreach ($records[$type] as $attributes) {
                foreach (['featured_image_path', 'cover_image_path', 'audio_path'] as $field) {
                    if (filled($attributes[$field] ?? null)) {
                        $paths[] = $this->media->validatePath($attributes[$field]);
                    }
                }
            }
        }

        $paths = array_values(array_unique($paths));
        sort($paths);

        return $paths;
    }

    /** @return array<string, mixed> */
    public function decode(string $contents): array
    {
        $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('The public content archive is invalid.');
        }

        $archive = [];

        foreach ($decoded as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('The public content archive is invalid.');
            }

            $archive[$key] = $value;
        }

        return $archive;
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function attributes(Model $model, array $fields): array
    {
        return $this->only($model->getAttributes(), $fields);
    }

    /** @return list<array{name: string, type: string|null}> */
    private function tags(Post|Project|Episode $model): array
    {
        $tags = [];

        foreach ($model->tags()->get() as $tag) {
            if (! $tag instanceof Tag || ! is_string($tag->name)) {
                throw new InvalidArgumentException('Public content contains an invalid tag.');
            }

            $tags[] = ['name' => $tag->name, 'type' => is_string($tag->type) ? $tag->type : null];
        }

        return $tags;
    }

    /** @return array<string, mixed>|null */
    private function seo(Post|Project|Podcast|Episode $model): ?array
    {
        $seo = $model->seo;

        return $seo !== null && $seo->exists ? $this->attributes($seo, self::SEO_FIELDS) : null;
    }

    private function stagingAuthor(): User
    {
        $author = User::query()->firstOrNew([
            'email' => $this->stringConfig('content-sync.staging_author.email'),
        ]);

        if (! $author->exists) {
            $author->fill([
                'name' => $this->stringConfig('content-sync.staging_author.name'),
                'password' => Hash::make(Str::random(64)),
            ]);
        }

        $author->forceFill(['is_admin' => false]);
        $author->save();

        return $author;
    }

    private function unpublishExistingContent(): void
    {
        Post::query()->published()->update(['status' => PublishStatus::Draft->value, 'published_at' => null]);
        Project::query()->published()->update(['status' => PublishStatus::Draft->value]);
        Podcast::query()->active()->update(['is_active' => false]);
        Episode::query()->published()->update(['status' => PublishStatus::Draft->value, 'published_at' => null]);
        Video::query()->published()->update(['published_at' => null]);
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

    /** @param array<string, mixed> $attributes */
    private function stringValue(array $attributes, string $field): string
    {
        $value = $attributes[$field] ?? null;

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException("The public content archive contains an invalid {$field}.");
        }

        return $value;
    }

    /** @param array<string, mixed> $attributes */
    private function nullableStringValue(array $attributes, string $field): ?string
    {
        $value = $attributes[$field] ?? null;

        if ($value === null || is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException("The public content archive contains an invalid {$field}.");
    }

    /** @return array<string, mixed>|null */
    private function nullableRecord(mixed $value, string $description): ?array
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException("The public content archive contains invalid {$description}.");
        }

        $record = [];

        foreach ($value as $key => $item) {
            if (! is_string($key)) {
                throw new InvalidArgumentException("The public content archive contains invalid {$description}.");
            }

            $record[$key] = $item;
        }

        return $record;
    }

    private function stringConfig(string $key): string
    {
        $value = config($key);

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException("The {$key} configuration must be a non-empty string.");
        }

        return $value;
    }
}
