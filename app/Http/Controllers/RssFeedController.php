<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class RssFeedController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::published()
            ->latest('published_at')
            ->with('category')
            ->take(20)
            ->get();

        $siteUrl = url('/');
        $feedUrl = url('/rss');
        $lastBuild = $posts->first()?->published_at?->toRssString() ?? now()->toRssString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= "<channel>\n";
        $xml .= "<title>The Laravel Architect</title>\n";
        $xml .= "<link>{$siteUrl}</link>\n";
        $xml .= "<description>Deep dives into Laravel, PHP, architecture patterns, and the craft of building modern web applications.</description>\n";
        $xml .= "<language>en-us</language>\n";
        $xml .= "<lastBuildDate>{$lastBuild}</lastBuildDate>\n";
        $xml .= "<atom:link href=\"{$feedUrl}\" rel=\"self\" type=\"application/rss+xml\" />\n";

        foreach ($posts as $post) {
            $title = htmlspecialchars($post->title, ENT_XML1, 'UTF-8');
            $link = route('blog.show', $post);
            $description = htmlspecialchars($post->excerpt ?? '', ENT_XML1, 'UTF-8');
            $pubDate = $post->published_at->toRssString();

            $xml .= "<item>\n";
            $xml .= "<title>{$title}</title>\n";
            $xml .= "<link>{$link}</link>\n";
            $xml .= "<guid isPermaLink=\"true\">{$link}</guid>\n";
            $xml .= "<description>{$description}</description>\n";
            $xml .= "<pubDate>{$pubDate}</pubDate>\n";

            if ($post->category) {
                $category = htmlspecialchars($post->category->name, ENT_XML1, 'UTF-8');
                $xml .= "<category>{$category}</category>\n";
            }

            $xml .= "</item>\n";
        }

        $xml .= "</channel>\n</rss>";

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
