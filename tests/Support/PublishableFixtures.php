<?php

namespace Tests\Support;

use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Category;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\EditEpisode;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\EditPost;

class PublishableFixtures
{
    /**
     * Create a draft of the given publishable type with every required-to-publish detail filled in,
     * through the model factories (slugs come from the title).
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function ready(string $type, array $attributes = []): Post|Project|Episode|NewsletterIssue
    {
        return match ($type) {
            'post' => Post::factory()
                ->for(self::sharedCategory())
                ->create([
                    'title' => 'Ready post',
                    'excerpt' => 'Summary.',
                    'content' => 'Content.',
                    ...$attributes,
                ]),
            'project' => Project::factory()->create([
                'title' => 'Ready project',
                'description' => 'Description.',
                'content' => 'Case study.',
                ...self::fillableOnly(new Project, $attributes),
            ]),
            'episode' => Episode::factory()
                ->for(self::sharedPodcast())
                ->create([
                    'title' => 'Ready episode',
                    'description' => 'Description.',
                    'transistor_url' => 'https://share.transistor.fm/s/428dcd6b',
                    ...$attributes,
                ]),
            'newsletter issue' => NewsletterIssue::factory()->create([
                'title' => 'Ready issue',
                'content' => 'Content.',
                ...$attributes,
            ]),
            default => throw new InvalidArgumentException("Unknown publishable type [{$type}]."),
        };
    }

    /**
     * Keep only the attributes the model accepts, as the mass-assigned `create()` this helper
     * used to call did: shared datasets pass `published_at`, which projects do not have.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private static function fillableOnly(Model $model, array $attributes): array
    {
        return array_filter($attributes, $model->isFillable(...), ARRAY_FILTER_USE_KEY);
    }

    /** The one category every fixture post shares, so several posts can sit side by side. */
    private static function sharedCategory(): Category
    {
        return Category::query()->firstWhere('slug', 'laravel')
            ?? Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    }

    /** The one podcast every fixture episode shares. */
    private static function sharedPodcast(): Podcast
    {
        return Podcast::query()->firstWhere('slug', 'show')
            ?? Podcast::factory()->create(['name' => 'Show', 'slug' => 'show', 'description' => 'A show.']);
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
            'episode' => ['podcast_id' => null, 'description' => '', 'transistor_url' => null],
            'newsletter issue' => ['content' => ''],
            default => throw new InvalidArgumentException("Unknown publishable type [{$type}]."),
        };
    }
}
