<?php

declare(strict_types=1);

namespace App\ViewModels\Concerns;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use RalphJSmit\Laravel\SEO\Models\SEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * Lets a content page keep the SEO fields an editor saved in the admin (the record's laravel-seo
 * `seo` row, from HasSEO). The page's own values win and each saved field fills one the page
 * leaves null, the precedence laravel-seo's SEO::prepareForUsage() gives a model's dynamic SEO.
 */
trait AppliesStoredSeo
{
    private function withStoredSeo(Post|Project|Episode|NewsletterIssue $content, SEOData $page): SEOData
    {
        $seo = $content->seo;
        // Read the raw attributes, as laravel-seo does, so a missing column never throws.
        $attributes = $seo instanceof SEO ? $seo->getAttributes() : [];
        $stored = static fn (string $key): ?string => is_string($attributes[$key] ?? null) ? $attributes[$key] : null;

        return new SEOData(
            title: $page->title ?? $stored('title'),
            description: $page->description ?? $stored('description'),
            author: $page->author ?? $stored('author'),
            image: $page->image ?? $stored('image'),
            url: $page->url,
            published_time: $page->published_time ?? $content->created_at,
            modified_time: $page->modified_time ?? $content->updated_at,
            articleBody: $page->articleBody,
            section: $page->section,
            tags: $page->tags,
            schema: $page->schema,
            type: $page->type,
            locale: $page->locale,
            robots: $page->robots ?? $stored('robots'),
            canonical_url: $page->canonical_url ?? $stored('canonical_url'),
            openGraphTitle: $page->openGraphTitle,
            alternates: $page->alternates,
        );
    }
}
