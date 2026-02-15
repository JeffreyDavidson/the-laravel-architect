<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Podcast;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::published()->latest('published_at')->get();
        $categories = Category::has('posts')->get();
        $podcasts = Podcast::where('is_active', true)->with('episodes')->get();
        $projects = Project::all();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Static pages
        foreach ([
            ['url' => route('home'), 'priority' => '1.0', 'freq' => 'weekly'],
            ['url' => route('about'), 'priority' => '0.8', 'freq' => 'monthly'],
            ['url' => route('contact'), 'priority' => '0.7', 'freq' => 'monthly'],
            ['url' => route('uses'), 'priority' => '0.6', 'freq' => 'monthly'],
            ['url' => route('blog.index'), 'priority' => '0.9', 'freq' => 'weekly'],
            ['url' => route('podcast.index'), 'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => route('projects.index'), 'priority' => '0.8', 'freq' => 'monthly'],
        ] as $page) {
            $xml .= '<url>';
            $xml .= '<loc>' . $page['url'] . '</loc>';
            $xml .= '<changefreq>' . $page['freq'] . '</changefreq>';
            $xml .= '<priority>' . $page['priority'] . '</priority>';
            $xml .= '</url>';
        }

        // Blog posts
        foreach ($posts as $post) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('blog.show', $post) . '</loc>';
            $xml .= '<lastmod>' . $post->updated_at->toW3cString() . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        // Categories
        foreach ($categories as $category) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('blog.category', $category) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.5</priority>';
            $xml .= '</url>';
        }

        // Podcasts
        foreach ($podcasts as $podcast) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('podcast.show', $podcast) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';

            foreach ($podcast->episodes as $episode) {
                $xml .= '<url>';
                $xml .= '<loc>' . route('podcast.episode', [$podcast, $episode]) . '</loc>';
                $xml .= '<lastmod>' . $episode->updated_at->toW3cString() . '</lastmod>';
                $xml .= '<changefreq>monthly</changefreq>';
                $xml .= '<priority>0.6</priority>';
                $xml .= '</url>';
            }
        }

        // Projects
        foreach ($projects as $project) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('projects.show', $project) . '</loc>';
            $xml .= '<lastmod>' . $project->updated_at->toW3cString() . '</lastmod>';
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
