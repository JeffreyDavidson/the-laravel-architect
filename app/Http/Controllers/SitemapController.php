<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
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
        $podcasts = Podcast::where('is_active', true)->get();
        $projects = Project::published()->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Static pages
        foreach ([
            ['url' => route('home'), 'priority' => '1.0', 'freq' => 'weekly'],
            ['url' => route('about'), 'priority' => '0.8', 'freq' => 'monthly'],
            ['url' => route('contact'), 'priority' => '0.7', 'freq' => 'monthly'],
            ['url' => route('privacy'), 'priority' => '0.3', 'freq' => 'yearly'],
            ['url' => route('uses'), 'priority' => '0.6', 'freq' => 'monthly'],
            ['url' => route('blog.index'), 'priority' => '0.9', 'freq' => 'weekly'],
            ['url' => route('podcast.index'), 'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => route('projects.index'), 'priority' => '0.8', 'freq' => 'monthly'],
        ] as $page) {
            $xml .= '<url>';
            $xml .= '<loc>'.$page['url'].'</loc>';
            $xml .= '<changefreq>'.$page['freq'].'</changefreq>';
            $xml .= '<priority>'.$page['priority'].'</priority>';
            $xml .= '</url>';
        }

        // Blog posts
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

        // Categories
        foreach ($categories as $category) {
            $xml .= '<url>';
            $xml .= '<loc>'.route('blog.category', $category).'</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.5</priority>';
            $xml .= '</url>';
        }

        // Tags
        foreach ($tags as $tag) {
            $xml .= '<url>';
            $xml .= '<loc>'.route('blog.tag', $tag).'</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.5</priority>';
            $xml .= '</url>';
        }

        // Podcasts
        foreach ($podcasts as $podcast) {
            $xml .= '<url>';
            $xml .= '<loc>'.route('podcast.show', $podcast).'</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';

            foreach ($podcast->publishedEpisodes()->get() as $episode) {
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

        // Projects
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

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
