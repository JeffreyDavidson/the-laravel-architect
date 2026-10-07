<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Post;
use App\Queries\SitemapQuery;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\Tag;

final readonly class SitemapViewModel
{
    public function __construct(private SitemapQuery $query) {}

    /**
     * Every public URL with its change frequency, priority and, where the page shows
     * content, the latest modification date of that content.
     *
     * @return array{urls: list<array{loc: string, lastmod: CarbonInterface|null, changefreq: string, priority: string}>}
     */
    public function data(): array
    {
        $posts = $this->query->posts();
        $tags = $posts
            ->flatMap->tags
            ->unique('id');
        $categories = $this->query->categories();
        $podcasts = $this->query->podcasts();
        $projects = $this->query->projects();
        $issues = $this->query->newsletterIssues();
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

        $urls = [
            $this->url(route('home'), $latestContentUpdatedAt, 'weekly', '1.0'),
            $this->url(route('about'), null, 'monthly', '0.8'),
            $this->url(route('services'), null, 'monthly', '0.8'),
            $this->url(route('contact.create'), null, 'monthly', '0.7'),
            $this->url(route('privacy'), null, 'yearly', '0.3'),
            $this->url(route('uses'), null, 'monthly', '0.6'),
            $this->url(route('blog.index'), $this->latestUpdatedAt($posts), 'weekly', '0.9'),
            $this->url(route('podcast.index'), $latestPodcastUpdatedAt, 'weekly', '0.8'),
            $this->url(route('projects.index'), $this->latestUpdatedAt($projects), 'monthly', '0.8'),
            $this->url(route('newsletter.index'), $this->latestUpdatedAt($issues), 'weekly', '0.8'),
            $this->url(route('archive.index'), $latestContentUpdatedAt, 'weekly', '0.7'),
        ];

        foreach ($posts as $post) {
            $urls[] = $this->url(route('blog.show', $post), $post->updated_at, 'monthly', '0.7');
        }

        foreach ($categories as $category) {
            $updatedAt = $this->latestUpdatedAt(
                $posts->where('category_id', $category->id),
            );

            $urls[] = $this->url(route('blog.category', $category), $updatedAt, 'weekly', '0.5');
        }

        foreach ($tags as $tag) {
            if (! $tag instanceof Tag) {
                continue;
            }

            $updatedAt = $this->latestUpdatedAt(
                $posts->filter(fn (Post $post) => $post->tags->contains($tag)),
            );

            $urls[] = $this->url(route('blog.tag', $tag), $updatedAt, 'weekly', '0.5');
        }

        foreach ($podcasts as $podcast) {
            $updatedAt = $this->latestUpdatedAt(
                collect([$podcast])->concat($podcast->publishedEpisodes),
            );

            $urls[] = $this->url(route('podcast.show', $podcast), $updatedAt, 'weekly', '0.7');

            foreach ($podcast->publishedEpisodes as $episode) {
                $urls[] = $this->url(route('podcast.episode', [$podcast, $episode]), $episode->updated_at, 'monthly', '0.6');
            }
        }

        foreach ($projects as $project) {
            $urls[] = $this->url(route('projects.show', $project), $project->updated_at, 'monthly', '0.6');
        }

        foreach ($issues as $issue) {
            $urls[] = $this->url(route('newsletter.issue', $issue), $issue->updated_at, 'monthly', '0.6');
        }

        return ['urls' => $urls];
    }

    /** @return array{loc: string, lastmod: CarbonInterface|null, changefreq: string, priority: string} */
    private function url(string $loc, ?CarbonInterface $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
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
