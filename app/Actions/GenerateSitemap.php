<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Category;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\Tag;

final class GenerateSitemap
{
    public function handle(): string
    {
        $posts = Post::query()->published()
            ->select(['id', 'slug', 'category_id', 'published_at', 'updated_at'])
            ->with('tags')
            ->latest('published_at')
            ->get();
        $tags = $posts
            ->flatMap->tags
            ->unique('id');
        $categories = Category::query()
            ->whereHas('publishedPosts')
            ->get();
        $podcasts = Podcast::query()
            ->active()
            ->with('publishedEpisodes:id,podcast_id,slug,updated_at')
            ->get();
        $projects = Project::query()->published()
            ->get(['id', 'slug', 'updated_at']);
        $issues = NewsletterIssue::query()->published()
            ->get(['id', 'slug', 'updated_at']);
        $podcastModels = [];

        foreach ($podcasts as $podcast) {
            $podcastModels[] = $podcast;

            foreach ($podcast->publishedEpisodes as $episode) {
                $podcastModels[] = $episode;
            }
        }

        $latestPodcastUpdatedAt = $this->latestUpdatedAt($podcastModels);
        $latestContentUpdatedAt = $this->latestUpdatedAt([
            ...$posts,
            ...$projects,
            ...$issues,
            ...$podcastModels,
        ]);

        $urls = [];

        foreach ([
            ['url' => route('home'), 'priority' => '1.0', 'freq' => 'weekly', 'lastmod' => $latestContentUpdatedAt],
            ['url' => route('about'), 'priority' => '0.8', 'freq' => 'monthly', 'lastmod' => null],
            ['url' => route('services'), 'priority' => '0.8', 'freq' => 'monthly', 'lastmod' => null],
            ['url' => route('contact.create'), 'priority' => '0.7', 'freq' => 'monthly', 'lastmod' => null],
            ['url' => route('privacy'), 'priority' => '0.3', 'freq' => 'yearly', 'lastmod' => null],
            ['url' => route('uses'), 'priority' => '0.6', 'freq' => 'monthly', 'lastmod' => null],
            ['url' => route('blog.index'), 'priority' => '0.9', 'freq' => 'weekly', 'lastmod' => $this->latestUpdatedAt($posts)],
            ['url' => route('podcast.index'), 'priority' => '0.8', 'freq' => 'weekly', 'lastmod' => $latestPodcastUpdatedAt],
            ['url' => route('projects.index'), 'priority' => '0.8', 'freq' => 'monthly', 'lastmod' => $this->latestUpdatedAt($projects)],
            ['url' => route('newsletter.index'), 'priority' => '0.8', 'freq' => 'weekly', 'lastmod' => $this->latestUpdatedAt($issues)],
            ['url' => route('archive.index'), 'priority' => '0.7', 'freq' => 'weekly', 'lastmod' => $latestContentUpdatedAt],
        ] as $page) {
            $urls[] = $this->urlElement($page['url'], $page['lastmod'], $page['freq'], $page['priority']);
        }

        foreach ($posts as $post) {
            $urls[] = $this->urlElement(route('blog.show', $post), $post->updated_at, 'monthly', '0.7');
        }

        foreach ($categories as $category) {
            $updatedAt = $this->latestUpdatedAt(
                $posts->where('category_id', $category->id),
            );

            $urls[] = $this->urlElement(route('blog.category', $category), $updatedAt, 'weekly', '0.5');
        }

        foreach ($tags as $tag) {
            if (! $tag instanceof Tag) {
                continue;
            }

            $updatedAt = $this->latestUpdatedAt(
                $posts->filter(fn (Post $post) => $post->tags->contains($tag)),
            );

            $urls[] = $this->urlElement(route('blog.tag', $tag), $updatedAt, 'weekly', '0.5');
        }

        foreach ($podcasts as $podcast) {
            $updatedAt = $this->latestUpdatedAt(
                collect([$podcast])->concat($podcast->publishedEpisodes),
            );

            $urls[] = $this->urlElement(route('podcast.show', $podcast), $updatedAt, 'weekly', '0.7');

            foreach ($podcast->publishedEpisodes as $episode) {
                $urls[] = $this->urlElement(route('podcast.episode', [$podcast, $episode]), $episode->updated_at, 'monthly', '0.6');
            }
        }

        foreach ($projects as $project) {
            $urls[] = $this->urlElement(route('projects.show', $project), $project->updated_at, 'monthly', '0.6');
        }

        foreach ($issues as $issue) {
            $urls[] = $this->urlElement(route('newsletter.issue', $issue), $issue->updated_at, 'monthly', '0.6');
        }

        $body = implode('', $urls);

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">{$body}</urlset>";
    }

    private function urlElement(string $loc, ?CarbonInterface $lastmod, string $changefreq, string $priority): string
    {
        $lastmodElement = $lastmod instanceof CarbonInterface
            ? "<lastmod>{$lastmod->toW3cString()}</lastmod>"
            : '';

        return "<url><loc>{$loc}</loc>{$lastmodElement}<changefreq>{$changefreq}</changefreq><priority>{$priority}</priority></url>";
    }

    /** @param iterable<array-key, Model> $models */
    private function latestUpdatedAt(iterable $models): ?CarbonInterface
    {
        $latestUpdatedAt = null;

        foreach ($models as $model) {
            $updatedAt = $model->getAttribute('updated_at');

            if (! $updatedAt instanceof CarbonInterface) {
                continue;
            }

            if (! $latestUpdatedAt instanceof CarbonInterface || $updatedAt->greaterThan($latestUpdatedAt)) {
                $latestUpdatedAt = $updatedAt;
            }
        }

        return $latestUpdatedAt;
    }
}
