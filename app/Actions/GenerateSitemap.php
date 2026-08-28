<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\Tag;

final class GenerateSitemap
{
    public function __invoke(): string
    {
        $posts = Post::published()
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
            ->where('is_active', true)
            ->with('publishedEpisodes')
            ->get();
        $projects = Project::published()->get();
        $podcastModels = [];

        foreach ($podcasts as $podcast) {
            $podcastModels[] = $podcast;

            foreach ($podcast->publishedEpisodes as $episode) {
                $podcastModels[] = $episode;
            }
        }

        $latestPodcastUpdatedAt = $this->latestUpdatedAt($podcastModels);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ([
            ['url' => route('home'), 'priority' => '1.0', 'freq' => 'weekly', 'lastmod' => null],
            ['url' => route('about'), 'priority' => '0.8', 'freq' => 'monthly', 'lastmod' => null],
            ['url' => route('contact'), 'priority' => '0.7', 'freq' => 'monthly', 'lastmod' => null],
            ['url' => route('privacy'), 'priority' => '0.3', 'freq' => 'yearly', 'lastmod' => null],
            ['url' => route('uses'), 'priority' => '0.6', 'freq' => 'monthly', 'lastmod' => null],
            ['url' => route('blog.index'), 'priority' => '0.9', 'freq' => 'weekly', 'lastmod' => $this->latestUpdatedAt($posts)],
            ['url' => route('podcast.index'), 'priority' => '0.8', 'freq' => 'weekly', 'lastmod' => $latestPodcastUpdatedAt],
            ['url' => route('projects.index'), 'priority' => '0.8', 'freq' => 'monthly', 'lastmod' => $this->latestUpdatedAt($projects)],
        ] as $page) {
            $xml .= '<url>';
            $xml .= '<loc>'.$page['url'].'</loc>';
            if ($page['lastmod'] !== null) {
                $xml .= '<lastmod>'.$page['lastmod']->toW3cString().'</lastmod>';
            }
            $xml .= '<changefreq>'.$page['freq'].'</changefreq>';
            $xml .= '<priority>'.$page['priority'].'</priority>';
            $xml .= '</url>';
        }

        foreach ($posts as $post) {
            $updatedAt = $post->updated_at;

            $xml .= '<url>';
            $xml .= '<loc>'.route('blog.show', $post).'</loc>';
            if ($updatedAt !== null) {
                $xml .= '<lastmod>'.$updatedAt->toW3cString().'</lastmod>';
            }
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        foreach ($categories as $category) {
            $updatedAt = $this->latestUpdatedAt(
                $posts->where('category_id', $category->id),
            );

            $xml .= '<url>';
            $xml .= '<loc>'.route('blog.category', $category).'</loc>';
            if ($updatedAt !== null) {
                $xml .= '<lastmod>'.$updatedAt->toW3cString().'</lastmod>';
            }
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.5</priority>';
            $xml .= '</url>';
        }

        foreach ($tags as $tag) {
            if (! $tag instanceof Tag) {
                continue;
            }

            $updatedAt = $this->latestUpdatedAt(
                $posts->filter(fn (Post $post) => $post->tags->contains($tag)),
            );

            $xml .= '<url>';
            $xml .= '<loc>'.route('blog.tag', $tag).'</loc>';
            if ($updatedAt !== null) {
                $xml .= '<lastmod>'.$updatedAt->toW3cString().'</lastmod>';
            }
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.5</priority>';
            $xml .= '</url>';
        }

        foreach ($podcasts as $podcast) {
            $updatedAt = $this->latestUpdatedAt(
                collect([$podcast])->concat($podcast->publishedEpisodes),
            );

            $xml .= '<url>';
            $xml .= '<loc>'.route('podcast.show', $podcast).'</loc>';
            if ($updatedAt !== null) {
                $xml .= '<lastmod>'.$updatedAt->toW3cString().'</lastmod>';
            }
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';

            foreach ($podcast->publishedEpisodes as $episode) {
                $updatedAt = $episode->updated_at;

                $xml .= '<url>';
                $xml .= '<loc>'.route('podcast.episode', [$podcast, $episode]).'</loc>';
                if ($updatedAt !== null) {
                    $xml .= '<lastmod>'.$updatedAt->toW3cString().'</lastmod>';
                }
                $xml .= '<changefreq>monthly</changefreq>';
                $xml .= '<priority>0.6</priority>';
                $xml .= '</url>';
            }
        }

        foreach ($projects as $project) {
            $updatedAt = $project->updated_at;

            $xml .= '<url>';
            $xml .= '<loc>'.route('projects.show', $project).'</loc>';
            if ($updatedAt !== null) {
                $xml .= '<lastmod>'.$updatedAt->toW3cString().'</lastmod>';
            }
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.6</priority>';
            $xml .= '</url>';
        }

        return $xml.'</urlset>';
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

            if ($latestUpdatedAt === null || $updatedAt->greaterThan($latestUpdatedAt)) {
                $latestUpdatedAt = $updatedAt;
            }
        }

        return $latestUpdatedAt;
    }
}
