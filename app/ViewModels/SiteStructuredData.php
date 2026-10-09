<?php

declare(strict_types=1);

namespace App\ViewModels;

use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\Support\Seo\JsonLd;

/**
 * The site-wide JSON-LD: the WebSite entity (with its author) at the start of every page's
 * graph, and the references and breadcrumbs page ViewModels use to point at the site and author.
 * The site layout and the BlogIndex component build the full graph through graph().
 */
final readonly class SiteStructuredData
{
    /**
     * The page's full JSON-LD graph: the site-wide entities, then the page's own nodes.
     *
     * @return list<array<string, mixed>>
     */
    public function graph(PageMeta $pageMeta): array
    {
        return [$this->website(), ...$pageMeta->structuredData];
    }

    /**
     * A reference to the site's author, the Person described on the About page.
     *
     * @return array{'@type': string, '@id': string}
     */
    public function authorReference(): array
    {
        return [
            '@type' => 'Person',
            '@id' => route('about').'#person',
        ];
    }

    /**
     * A fixed page of the site (home, about, contact and so on) as part of the website.
     *
     * @param  array<string, mixed>|null  $mainEntity
     * @return array<string, mixed>
     */
    public function page(string $type, string $name, string $url, ?array $mainEntity = null): array
    {
        $page = [
            '@type' => $type,
            '@id' => "{$url}#page",
            'name' => $name,
            'url' => $url,
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => route('home').'#website',
            ],
        ];

        if ($mainEntity !== null) {
            $page['mainEntity'] = $mainEntity;
        }

        return $page;
    }

    /**
     * The page's BreadcrumbList: Home, then the given trail.
     *
     * @param  list<array{name: string, url: string}>  $trail
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $trail): array
    {
        return JsonLd::breadcrumbList([
            ['name' => 'Home', 'url' => route('home')],
            ...$trail,
        ]);
    }

    /** @return array<string, mixed> */
    private function website(): array
    {
        $siteUrl = route('home');
        $authorUrl = route('about');

        return [
            '@type' => 'WebSite',
            '@id' => "{$siteUrl}#website",
            'name' => 'The Laravel Architect',
            'url' => $siteUrl,
            'author' => [
                '@type' => 'Person',
                '@id' => "{$authorUrl}#person",
                'name' => 'Jeffrey Davidson',
                'url' => $authorUrl,
            ],
        ];
    }
}
