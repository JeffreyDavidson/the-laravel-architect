<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Data\StructuredDataPage;
use App\Models\Post;
use Illuminate\Support\Facades\Date;

final readonly class ArticleSchemaBuilder
{
    public function __construct(private PostShareImage $postShareImage) {}

    /**
     * @param  list<array<string, mixed>>  $schemas
     */
    public function add(array &$schemas, StructuredDataPage $page, string $authorUrl): void
    {
        $post = $page->post;

        if ($page->routeName !== 'blog.show' || ! $post instanceof Post) {
            return;
        }

        $postUrl = route('blog.show', $post);
        $schemas[] = [
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
            'image' => $this->postShareImage->url($post),
        ];
    }
}
