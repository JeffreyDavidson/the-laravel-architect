<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Models\Post;
use Illuminate\Support\Facades\Date;

final class ArticleSchemaBuilder
{
    /**
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    public function add(array &$schemas, array $pageData, string $routeName, string $authorUrl): void
    {
        $post = $this->post($pageData);

        if ($routeName !== 'blog.show' || ! $post instanceof Post) {
            return;
        }

        $postUrl = route('blog.show', $post);
        $article = [
            '@type' => 'Article',
            '@id' => $postUrl.'#article',
            'url' => $postUrl,
            'headline' => $post->title,
            'datePublished' => $post->published_at !== null
                ? Date::parse($post->published_at)->toIso8601String()
                : null,
            'dateModified' => $post->updated_at !== null
                ? Date::parse($post->updated_at)->toIso8601String()
                : null,
            'author' => [
                '@type' => 'Person',
                '@id' => $authorUrl.'#person',
            ],
            'mainEntityOfPage' => $postUrl,
            'description' => $post->excerpt ?? '',
        ];

        if ($post->featured_image_url) {
            $article['image'] = $post->featured_image_url;
        }

        $schemas[] = $article;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function post(array $pageData): ?Post
    {
        $value = $pageData['post'] ?? null;

        return $value instanceof Post ? $value : null;
    }
}
