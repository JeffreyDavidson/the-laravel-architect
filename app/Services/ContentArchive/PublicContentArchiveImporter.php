<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

use App\Models\Category;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

class PublicContentArchiveImporter
{
    public function __construct(
        private readonly PublicContentArchiveValidator $validator,
        private readonly PublicContentArchiveRelations $relations,
    ) {}

    /**
     * @param  array<string, mixed>  $archive
     * @return array<string, int>
     */
    public function sync(array $archive): array
    {
        $records = $this->validator->validate($archive, PublicContentArchiveSchema::VERSION);

        return Model::withoutEvents(fn (): array => DB::transaction(function () use ($records): array {
            $this->unpublishExistingContent();
            $author = $this->stagingAuthor();

            $this->syncCategories($records['categories']);
            $this->syncPodcasts($records['podcasts']);
            $this->syncPosts($records['posts'], $author);
            $this->syncProjects($records['projects']);
            $this->syncEpisodes($records['episodes']);
            $this->syncNewsletterIssues($records['newsletter_issues']);
            $this->syncVideos($records['videos']);

            return collect(['categories', 'posts', 'projects', 'podcasts', 'episodes', 'newsletter_issues', 'videos'])
                ->mapWithKeys(fn (string $type): array => [$type => count($records[$type])])
                ->all();
        }));
    }

    /**
     * The archive's validated media paths, unique and sorted. Every archived media file is an image.
     *
     * @param  array<string, mixed>  $archive
     * @return list<string>
     */
    public function mediaPaths(array $archive): array
    {
        $records = $this->validator->validate($archive, PublicContentArchiveSchema::VERSION);

        $paths = [];

        foreach (['posts', 'projects', 'podcasts', 'episodes'] as $type) {
            foreach ($records[$type] as $attributes) {
                foreach (['featured_image_path', 'cover_image_path'] as $field) {
                    if (filled($attributes[$field] ?? null)) {
                        $paths[] = $this->validator->validateMediaPath($attributes[$field]);
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

    /** @param list<array<string, mixed>> $records */
    private function syncCategories(array $records): void
    {
        foreach ($records as $attributes) {
            Category::updateOrCreate(
                ['slug' => $this->stringValue($attributes, 'slug')],
                PublicContentArchiveSchema::only($attributes, ['name', 'description']),
            );
        }
    }

    /** @param list<array<string, mixed>> $records */
    private function syncPodcasts(array $records): void
    {
        foreach ($records as $attributes) {
            $podcast = Podcast::withTrashed()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
            $podcast->setAttribute('deleted_at', null);
            $podcast->fill([...PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::PODCAST_FIELDS), 'is_active' => true]);
            $podcast->save();
            $this->relations->syncSeo($podcast, $this->nullableRecord($attributes['seo'] ?? null, 'podcast SEO'), PublicContentArchiveSchema::SEO_FIELDS);
        }
    }

    /** @param list<array<string, mixed>> $records */
    private function syncPosts(array $records, User $author): void
    {
        foreach ($records as $attributes) {
            $post = Post::withTrashed()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
            $post->setAttribute('deleted_at', null);
            $post->fill([
                ...PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::POST_FIELDS),
                'category_id' => Category::query()->where('slug', $this->nullableStringValue($attributes, 'category_slug'))
                    ->value('id'),
                'user_id' => $author->getKey(),
                'status' => PublishStatus::Published,
                'review_notes' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);
            $post->save();
            $this->relations->syncTags($post, $this->relations->tagRecords($attributes['tags'] ?? []));
            $this->relations->syncSeo($post, $this->nullableRecord($attributes['seo'] ?? null, 'post SEO'), PublicContentArchiveSchema::SEO_FIELDS);
        }
    }

    /** @param list<array<string, mixed>> $records */
    private function syncProjects(array $records): void
    {
        foreach ($records as $attributes) {
            $project = Project::withTrashed()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
            $project->setAttribute('deleted_at', null);
            $project->fill([...PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::PROJECT_FIELDS), 'status' => PublishStatus::Published]);
            $project->save();
            $this->relations->syncTags($project, $this->relations->tagRecords($attributes['tags'] ?? []));
            $this->relations->syncSeo($project, $this->nullableRecord($attributes['seo'] ?? null, 'project SEO'), PublicContentArchiveSchema::SEO_FIELDS);
        }
    }

    /** @param list<array<string, mixed>> $records */
    private function syncEpisodes(array $records): void
    {
        foreach ($records as $attributes) {
            $episode = Episode::withTrashed()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
            $episode->setAttribute('deleted_at', null);
            $episode->fill([
                ...PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::EPISODE_FIELDS),
                'podcast_id' => Podcast::query()->where('slug', $this->nullableStringValue($attributes, 'podcast_slug'))
                    ->value('id'),
                'status' => PublishStatus::Published,
            ]);
            $episode->save();
            $this->relations->syncTags($episode, $this->relations->tagRecords($attributes['tags'] ?? []));
            $this->relations->syncSeo($episode, $this->nullableRecord($attributes['seo'] ?? null, 'episode SEO'), PublicContentArchiveSchema::SEO_FIELDS);
        }
    }

    /** @param list<array<string, mixed>> $records */
    private function syncNewsletterIssues(array $records): void
    {
        foreach ($records as $attributes) {
            $issue = NewsletterIssue::withTrashed()->firstOrNew(['slug' => $this->stringValue($attributes, 'slug')]);
            $issue->setAttribute('deleted_at', null);
            $issue->fill([
                ...PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::NEWSLETTER_ISSUE_FIELDS),
                'status' => PublishStatus::Published,
            ]);
            $issue->save();
            $this->relations->syncSeo($issue, $this->nullableRecord($attributes['seo'] ?? null, 'newsletter issue SEO'), PublicContentArchiveSchema::SEO_FIELDS);
        }
    }

    /** @param list<array<string, mixed>> $records */
    private function syncVideos(array $records): void
    {
        foreach ($records as $attributes) {
            Video::updateOrCreate(
                ['youtube_id' => $this->stringValue($attributes, 'youtube_id')],
                PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::VIDEO_FIELDS),
            );
        }
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
        Post::query()->published()
            ->update(['status' => PublishStatus::Draft->value, 'published_at' => null]);
        Project::query()->published()
            ->update(['status' => PublishStatus::Draft->value]);
        Podcast::query()->active()
            ->update(['is_active' => false]);
        Episode::query()->published()
            ->update(['status' => PublishStatus::Draft->value, 'published_at' => null]);
        NewsletterIssue::query()->published()
            ->update(['status' => PublishStatus::Draft->value, 'published_at' => null]);
        Video::query()->published()
            ->update(['published_at' => null]);
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
