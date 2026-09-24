<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScaleTestContentWorkflow
{
    private const int POST_COUNT = 100;

    private const int PROJECT_COUNT = 50;

    private const int EPISODE_COUNT = 300;

    private const string AUTHOR_EMAIL = 'scale-test-author@example.invalid';

    private const string CATEGORY_SLUG = 'scale-test-category';

    private const string PODCAST_SLUG = 'scale-test-podcast';

    /** @return array<string, int> */
    public function seed(): array
    {
        return DB::transaction(function (): array {
            $now = now();

            DB::table('users')->insertOrIgnore([
                'name' => 'Scale Test Author',
                'email' => self::AUTHOR_EMAIL,
                'password' => bcrypt(Str::random(64)),
                'is_admin' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $authorQuery = DB::table('users');
            $authorQuery = $authorQuery->where('email', self::AUTHOR_EMAIL);
            $author = $authorQuery->first(['id', 'name', 'is_admin']);

            if ($author === null) {
                throw new \RuntimeException('Unable to create the scale-test author.');
            }

            $authorName = $author->name;
            $authorIsAdmin = (bool) $author->is_admin;

            if ($authorName !== 'Scale Test Author' || $authorIsAdmin) {
                throw new \RuntimeException('The reserved scale-test author identity is already in use.');
            }

            $authorId = $author->id;

            DB::table('categories')->updateOrInsert(
                ['slug' => self::CATEGORY_SLUG],
                [
                    'name' => 'Scale Test Content',
                    'description' => 'Synthetic content for local performance checks.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            $categoryQuery = DB::table('categories');
            $categoryQuery = $categoryQuery->where('slug', self::CATEGORY_SLUG);
            $categoryId = $categoryQuery->value('id');

            DB::table('podcasts')->updateOrInsert(
                ['slug' => self::PODCAST_SLUG],
                [
                    'name' => 'Scale Test Podcast',
                    'description' => 'Synthetic episodes for local performance checks.',
                    'long_description' => 'This podcast contains generated, non-editorial scale-test records.',
                    'color' => '#6366f1',
                    'is_active' => true,
                    'sort_order' => 999,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            $podcastQuery = DB::table('podcasts');
            $podcastQuery = $podcastQuery->where('slug', self::PODCAST_SLUG);
            $podcastId = $podcastQuery->value('id');

            if (! is_int($authorId) || ! is_int($categoryId) || ! is_int($podcastId)) {
                throw new \RuntimeException('Unable to create the scale-test content owners.');
            }

            $posts = [];

            for ($number = 1; $number <= self::POST_COUNT; $number++) {
                $formattedNumber = str_pad((string) $number, 3, '0', STR_PAD_LEFT);
                $posts[] = [
                    'title' => "Scale test article {$formattedNumber}: Laravel application patterns",
                    'slug' => "scale-test-post-{$formattedNumber}",
                    'excerpt' => 'Generated editorial content for local archive and listing performance checks.',
                    'content' => $this->articleContent($number),
                    'category_id' => $categoryId,
                    'user_id' => $authorId,
                    'status' => 'published',
                    'published_at' => $this->publishedAt($now, self::POST_COUNT - $number),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->upsertInChunks('posts', $posts, 'slug', [
                'title', 'excerpt', 'content', 'category_id', 'user_id', 'status', 'published_at', 'updated_at',
            ]);

            $projects = [];
            $technologies = ['Laravel', 'Livewire', 'PHP', 'SQLite', 'Pest'];

            for ($number = 1; $number <= self::PROJECT_COUNT; $number++) {
                $formattedNumber = str_pad((string) $number, 3, '0', STR_PAD_LEFT);
                $projects[] = [
                    'title' => "Scale Test Project {$formattedNumber}",
                    'slug' => "scale-test-project-{$formattedNumber}",
                    'description' => 'A generated portfolio record for local listing and filter performance checks.',
                    'content' => 'Synthetic project details. This record is not a real client project or endorsement.',
                    'url' => null,
                    'github_url' => null,
                    'tech_stack' => json_encode(array_slice($technologies, 0, 1 + ($number % count($technologies))), JSON_THROW_ON_ERROR),
                    'is_featured' => false,
                    'sort_order' => 1000 + $number,
                    'status' => 'published',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->upsertInChunks('projects', $projects, 'slug', [
                'title', 'description', 'content', 'url', 'github_url', 'tech_stack', 'is_featured', 'sort_order', 'status', 'updated_at',
            ]);

            $episodes = [];

            for ($number = 1; $number <= self::EPISODE_COUNT; $number++) {
                $formattedNumber = str_pad((string) $number, 3, '0', STR_PAD_LEFT);
                $episodes[] = [
                    'podcast_id' => $podcastId,
                    'title' => "Scale Test Episode {$formattedNumber}: Building reliable Laravel applications",
                    'slug' => "scale-test-episode-{$formattedNumber}",
                    'episode_number' => $number,
                    'season_number' => (int) ceil($number / 12),
                    'description' => 'A generated podcast episode for local archive and pagination performance checks.',
                    'show_notes' => 'Synthetic show notes. No audio or external service is associated with this record.',
                    'transcript' => $this->episodeTranscript($number),
                    'audio_url' => null,
                    'audio_path' => null,
                    'embed_url' => null,
                    'youtube_url' => null,
                    'duration_minutes' => 25 + ($number % 20),
                    'status' => 'published',
                    'published_at' => $this->publishedAt($now, self::EPISODE_COUNT - $number),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->upsertInChunks('episodes', $episodes, 'slug', [
                'podcast_id', 'title', 'episode_number', 'season_number', 'description', 'show_notes', 'transcript',
                'audio_url', 'audio_path', 'embed_url', 'youtube_url', 'duration_minutes', 'status', 'published_at', 'updated_at',
            ]);

            return [
                'podcasts' => 1,
                'episodes' => self::EPISODE_COUNT,
                'posts' => self::POST_COUNT,
                'projects' => self::PROJECT_COUNT,
            ];
        });
    }

    /** @return array<string, int> */
    public function clear(): array
    {
        return DB::transaction(function (): array {
            $episodeQuery = DB::table('episodes');
            $episodeQuery = $episodeQuery->where('slug', 'like', 'scale-test-episode-%');
            $episodes = $episodeQuery->delete();

            $postQuery = DB::table('posts');
            $postQuery = $postQuery->where('slug', 'like', 'scale-test-post-%');
            $posts = $postQuery->delete();

            $projectQuery = DB::table('projects');
            $projectQuery = $projectQuery->where('slug', 'like', 'scale-test-project-%');
            $projects = $projectQuery->delete();

            $podcastQuery = DB::table('podcasts');
            $podcastQuery = $podcastQuery->where('slug', self::PODCAST_SLUG);
            $podcasts = $podcastQuery->delete();

            $categoryQuery = DB::table('categories');
            $categoryQuery = $categoryQuery->where('slug', self::CATEGORY_SLUG);
            $categoryQuery = $categoryQuery->whereNotExists(function (Builder $query): void {
                $query = $query->selectRaw('1');
                $query = $query->from('posts');
                $query->whereColumn('posts.category_id', 'categories.id');
            });
            $categories = $categoryQuery->delete();

            $authorQuery = DB::table('users');
            $authorQuery = $authorQuery->where('email', self::AUTHOR_EMAIL);
            $authorQuery = $authorQuery->whereNotExists(function (Builder $query): void {
                $query = $query->selectRaw('1');
                $query = $query->from('posts');
                $query->whereColumn('posts.user_id', 'users.id');
            });
            $authors = $authorQuery->delete();

            return [
                'episodes' => $episodes,
                'posts' => $posts,
                'projects' => $projects,
                'podcasts' => $podcasts,
                'categories' => $categories,
                'authors' => $authors,
            ];
        });
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  non-empty-string  $uniqueBy
     * @param  list<string>  $update
     */
    private function upsertInChunks(string $table, array $rows, string $uniqueBy, array $update): void
    {
        foreach (array_chunk($rows, 40) as $chunk) {
            DB::table($table)->upsert($chunk, $uniqueBy, $update);
        }
    }

    private function articleContent(int $number): string
    {
        $topic = match ($number % 4) {
            0 => 'database queries and eager loading',
            1 => 'testing application behavior',
            2 => 'deployment and release practices',
            default => 'maintainable Laravel architecture',
        };

        return "## Working with {$topic}\n\n"
            .'This generated article explores practical decisions for a Laravel application. '
            .'It includes enough realistic text to exercise archive rendering, excerpts, and pagination. '
            .'The content is synthetic and is intended only for development and performance checks.';
    }

    private function episodeTranscript(int $number): string
    {
        return "Host: Welcome to generated scale-test episode {$number}.\n\n"
            .'Guest: We are discussing application structure, safe data access, and how to verify changes. '
            .'This synthetic transcript exercises episode detail pages without referencing a real person or recording.';
    }

    private function publishedAt(Carbon $now, int $daysAgo): Carbon
    {
        $publishedAt = $now->copy();

        return $publishedAt->subDays($daysAgo);
    }
}
