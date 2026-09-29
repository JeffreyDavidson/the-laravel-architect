<?php

namespace Tests\Support;

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Category;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use InvalidArgumentException;

class PublishableFixtures
{
    /**
     * Create a draft of the given publishable type with every required-to-publish detail filled in.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function ready(string $type, array $attributes = []): Post|Project|Episode|NewsletterIssue
    {
        return match ($type) {
            'post' => Post::query()->create([
                'title' => 'Ready post',
                'slug' => 'ready-post',
                'excerpt' => 'Summary.',
                'content' => 'Content.',
                'category_id' => Category::query()
                    ->firstOrCreate(['slug' => 'laravel'], ['name' => 'Laravel'])
                    ->id,
                'user_id' => User::factory()
                    ->create()
                    ->id,
                'status' => PublishStatus::Draft,
                ...$attributes,
            ]),
            'project' => Project::query()->create([
                'title' => 'Ready project',
                'slug' => 'ready-project',
                'description' => 'Description.',
                'content' => 'Case study.',
                'status' => PublishStatus::Draft,
                ...$attributes,
            ]),
            'episode' => Episode::query()->create([
                'podcast_id' => Podcast::query()
                    ->create(['name' => 'Show', 'slug' => 'show', 'description' => 'A show.'])
                    ->id,
                'title' => 'Ready episode',
                'slug' => 'ready-episode',
                'description' => 'Description.',
                'audio_url' => 'https://example.com/audio.mp3',
                'status' => PublishStatus::Draft,
                ...$attributes,
            ]),
            'newsletter issue' => NewsletterIssue::query()->create([
                'title' => 'Ready issue',
                'slug' => 'ready-issue',
                'content' => 'Content.',
                'status' => PublishStatus::Draft,
                ...$attributes,
            ]),
            default => throw new InvalidArgumentException("Unknown publishable type [{$type}]."),
        };
    }

    /**
     * A ready-to-publish post draft.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function readyPost(array $attributes = []): Post
    {
        $post = self::ready('post', $attributes);

        if (! $post instanceof Post) {
            throw new InvalidArgumentException('The post fixture did not create a post.');
        }

        return $post;
    }

    /**
     * The Filament edit page for the given publishable type.
     *
     * @return class-string
     */
    public static function editPage(string $type): string
    {
        return match ($type) {
            'post' => EditPost::class,
            'project' => EditProject::class,
            'episode' => EditEpisode::class,
            'newsletter issue' => EditNewsletterIssue::class,
            default => throw new InvalidArgumentException("Unknown publishable type [{$type}]."),
        };
    }

    /**
     * Attributes that blank out every required-to-publish detail for the given type.
     *
     * @return array<string, mixed>
     */
    public static function withoutRequiredDetails(string $type): array
    {
        return match ($type) {
            'post' => ['content' => '', 'excerpt' => '', 'category_id' => null],
            'project' => ['description' => '', 'content' => ''],
            'episode' => ['podcast_id' => null, 'description' => '', 'audio_url' => null],
            'newsletter issue' => ['content' => ''],
            default => throw new InvalidArgumentException("Unknown publishable type [{$type}]."),
        };
    }
}
