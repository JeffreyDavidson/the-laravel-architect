<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Request;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class StructuredDataBuilder
{
    public function __construct(private readonly Request $request) {}

    /**
     * @param  array<string, mixed>  $pageData
     * @return list<array<string, mixed>>
     */
    public function build(array $pageData): array
    {
        $siteUrl = route('home');
        $authorUrl = route('about');
        $schemas = [[
            '@type' => 'WebSite',
            '@id' => $siteUrl.'#website',
            'name' => 'The Laravel Architect',
            'url' => $siteUrl,
            'author' => [
                '@type' => 'Person',
                '@id' => $authorUrl.'#person',
                'name' => 'Jeffrey Davidson',
                'url' => $authorUrl,
            ],
        ]];

        $this->addStaticPageSchema($schemas, $siteUrl, $authorUrl);
        $this->addArticleSchema($schemas, $pageData, $authorUrl);
        $this->addPodcastSchemas($schemas, $pageData, $authorUrl);
        $this->addProjectSchema($schemas, $pageData, $authorUrl);
        $this->addCollectionSchemas($schemas, $pageData);
        $this->addBreadcrumbSchema($schemas, $pageData, $siteUrl);

        return $schemas;
    }

    /**
     * @param  list<array<string, mixed>>  $schemas
     */
    private function addStaticPageSchema(array &$schemas, string $siteUrl, string $authorUrl): void
    {
        $staticPage = match (true) {
            $this->request->routeIs('home') => ['type' => 'WebPage', 'name' => 'The Laravel Architect', 'url' => $siteUrl],
            $this->request->routeIs('about') => ['type' => 'ProfilePage', 'name' => 'About', 'url' => route('about')],
            $this->request->routeIs('contact') => ['type' => 'ContactPage', 'name' => 'Contact', 'url' => route('contact')],
            $this->request->routeIs('privacy') => ['type' => 'WebPage', 'name' => 'Privacy', 'url' => route('privacy')],
            $this->request->routeIs('uses') => ['type' => 'WebPage', 'name' => 'Uses', 'url' => route('uses')],
            default => null,
        };

        if ($staticPage === null) {
            return;
        }

        $pageSchema = [
            '@type' => $staticPage['type'],
            '@id' => $staticPage['url'].'#page',
            'name' => $staticPage['name'],
            'url' => $staticPage['url'],
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => $siteUrl.'#website',
            ],
        ];

        if ($this->request->routeIs('about')) {
            $pageSchema['mainEntity'] = [
                '@type' => 'Person',
                '@id' => $authorUrl.'#person',
            ];
        }

        $schemas[] = $pageSchema;
    }

    /**
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    private function addArticleSchema(array &$schemas, array $pageData, string $authorUrl): void
    {
        $post = $pageData['post'] ?? null;

        if (! $this->request->routeIs('blog.show') || ! $post instanceof Post) {
            return;
        }

        $postUrl = route('blog.show', $post);
        $article = [
            '@type' => 'Article',
            '@id' => $postUrl.'#article',
            'url' => $postUrl,
            'headline' => $post->title,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
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
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    private function addPodcastSchemas(array &$schemas, array $pageData, string $authorUrl): void
    {
        $podcast = $pageData['podcast'] ?? null;
        $episode = $pageData['episode'] ?? null;

        if (! $this->request->routeIs('podcast.show', 'podcast.episode') || ! $podcast instanceof Podcast) {
            return;
        }

        $podcastUrl = route('podcast.show', $podcast);
        $podcastSeries = [
            '@type' => 'PodcastSeries',
            '@id' => $podcastUrl.'#podcast',
            'name' => $podcast->name,
            'url' => $podcastUrl,
            'author' => [
                '@type' => 'Person',
                '@id' => $authorUrl.'#person',
            ],
        ];

        if ($podcast->description) {
            $podcastSeries['description'] = $podcast->description;
        }

        if ($podcast->cover_image_url) {
            $podcastSeries['image'] = $podcast->cover_image_url;
        }

        $schemas[] = $podcastSeries;

        if (! $this->request->routeIs('podcast.episode') || ! $episode instanceof Episode) {
            return;
        }

        $episodeUrl = route('podcast.episode', [$podcast, $episode]);
        $podcastEpisode = [
            '@type' => 'PodcastEpisode',
            '@id' => $episodeUrl.'#episode',
            'name' => $episode->title,
            'url' => $episodeUrl,
            'mainEntityOfPage' => $episodeUrl,
            'partOfSeries' => [
                '@type' => 'PodcastSeries',
                '@id' => $podcastUrl.'#podcast',
            ],
        ];

        if ($episode->description) {
            $podcastEpisode['description'] = $episode->description;
        }

        if ($episode->published_at) {
            $podcastEpisode['datePublished'] = $episode->published_at->toIso8601String();
        }

        if ($episode->episode_number !== null) {
            $podcastEpisode['episodeNumber'] = $episode->episode_number;
        }

        if ($episode->duration_minutes) {
            $podcastEpisode['duration'] = 'PT'.$episode->duration_minutes.'M';
        }

        if ($episode->publicAudioUrl()) {
            $podcastEpisode['associatedMedia'] = [
                '@type' => 'MediaObject',
                'contentUrl' => $episode->publicAudioUrl(),
            ];
        }

        $schemas[] = $podcastEpisode;
    }

    /**
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    private function addProjectSchema(array &$schemas, array $pageData, string $authorUrl): void
    {
        $project = $pageData['project'] ?? null;

        if (! $this->request->routeIs('projects.show') || ! $project instanceof Project) {
            return;
        }

        $projectUrl = route('projects.show', $project);
        $projectCaseStudy = [
            '@type' => 'CreativeWork',
            '@id' => $projectUrl.'#project',
            'name' => $project->title,
            'url' => $projectUrl,
            'mainEntityOfPage' => $projectUrl,
            'description' => $project->description,
            'author' => [
                '@type' => 'Person',
                '@id' => $authorUrl.'#person',
            ],
        ];

        if ($project->featured_image_url) {
            $projectCaseStudy['image'] = $project->featured_image_url;
        }

        if ($project->tech_stack) {
            $projectCaseStudy['keywords'] = implode(', ', $project->tech_stack);
        }

        if ($project->url) {
            $projectCaseStudy['sameAs'] = [$project->url];
        }

        $schemas[] = $projectCaseStudy;
    }

    /**
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    private function addCollectionSchemas(array &$schemas, array $pageData): void
    {
        $seoSource = $pageData['seoSource'] ?? null;
        $collectionPage = null;
        $collectionItems = [];
        $positionOffset = 0;

        if ($this->request->routeIs('blog.index') && isset($pageData['posts'])) {
            $posts = $pageData['posts'];
            $collectionPage = [
                'name' => isset($pageData['selectedCategory']) && $pageData['selectedCategory']
                    ? $pageData['selectedCategory']->name.' Articles'
                    : 'Blog',
                'url' => $this->canonicalUrl($seoSource, route('blog.index')),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($this->request->routeIs('blog.category') && isset($pageData['category'], $pageData['posts'])) {
            $posts = $pageData['posts'];
            $category = $pageData['category'];
            $collectionPage = [
                'name' => $category->name.' Articles',
                'url' => $this->canonicalUrl($seoSource, route('blog.category', $category)),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($this->request->routeIs('blog.tag') && isset($pageData['tag'], $pageData['posts'])) {
            $posts = $pageData['posts'];
            $tag = $pageData['tag'];
            $collectionPage = [
                'name' => $tag->name.' Articles',
                'url' => $this->canonicalUrl($seoSource, route('blog.tag', $tag)),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($this->request->routeIs('projects.index') && isset($pageData['projects'])) {
            $collectionPage = ['name' => 'Projects', 'url' => route('projects.index')];

            foreach ($pageData['projects'] as $project) {
                $collectionItems[] = ['name' => $project->title, 'url' => route('projects.show', $project)];
            }
        } elseif ($this->request->routeIs('podcast.show') && isset($pageData['podcast'], $pageData['episodes'])) {
            $podcast = $pageData['podcast'];
            $episodes = $pageData['episodes'];
            $collectionPage = [
                'name' => $podcast->name.' Episodes',
                'url' => $this->canonicalUrl($seoSource, route('podcast.show', $podcast)),
            ];
            $positionOffset = ($episodes->currentPage() - 1) * $episodes->perPage();

            foreach ($episodes as $episode) {
                $collectionItems[] = [
                    'name' => $episode->title,
                    'url' => route('podcast.episode', [$podcast, $episode]),
                ];
            }
        } elseif ($this->request->routeIs('podcast.index')) {
            $collectionPage = ['name' => 'Podcast', 'url' => route('podcast.index')];
            $podcast = $pageData['podcast'] ?? null;

            if ($podcast instanceof Podcast) {
                $collectionItems[] = ['name' => $podcast->name, 'url' => route('podcast.show', $podcast)];
            }
        } elseif ($this->request->routeIs('archive.index') && isset($pageData['items'])) {
            $items = $pageData['items'];
            $collectionPage = [
                'name' => 'Archive',
                'url' => $this->canonicalUrl($seoSource, route('archive.index')),
            ];
            $positionOffset = ($items->currentPage() - 1) * $items->perPage();

            foreach ($items as $item) {
                $collectionItems[] = ['name' => $item['title'], 'url' => $item['url']];
            }
        }

        if ($collectionPage === null) {
            return;
        }

        $itemListId = $collectionPage['url'].'#items';
        $schemas[] = [
            '@type' => 'CollectionPage',
            '@id' => $collectionPage['url'].'#collection',
            'name' => $collectionPage['name'],
            'url' => $collectionPage['url'],
            'mainEntity' => [
                '@type' => 'ItemList',
                '@id' => $itemListId,
            ],
        ];

        $itemListElements = [];
        foreach ($collectionItems as $index => $item) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $positionOffset + $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        $schemas[] = [
            '@type' => 'ItemList',
            '@id' => $itemListId,
            'numberOfItems' => count($itemListElements),
            'itemListElement' => $itemListElements,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    private function addBreadcrumbSchema(array &$schemas, array $pageData, string $siteUrl): void
    {
        $seoSource = $pageData['seoSource'] ?? null;
        $post = $pageData['post'] ?? null;
        $category = $pageData['category'] ?? null;
        $tag = $pageData['tag'] ?? null;
        $project = $pageData['project'] ?? null;
        $podcast = $pageData['podcast'] ?? null;
        $episode = $pageData['episode'] ?? null;
        $breadcrumbs = [['name' => 'Home', 'url' => $siteUrl]];

        if ($this->request->routeIs('blog.index')) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => $this->canonicalUrl($seoSource, route('blog.index'))];
        } elseif ($this->request->routeIs('blog.show') && $post instanceof Post) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => route('blog.index')];
            $breadcrumbs[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
        } elseif ($this->request->routeIs('blog.category') && $category !== null) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => route('blog.index')];
            $breadcrumbs[] = ['name' => $category->name, 'url' => $this->canonicalUrl($seoSource, route('blog.category', $category))];
        } elseif ($this->request->routeIs('blog.tag') && $tag !== null) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => route('blog.index')];
            $breadcrumbs[] = ['name' => $tag->name, 'url' => $this->canonicalUrl($seoSource, route('blog.tag', $tag))];
        } elseif ($this->request->routeIs('projects.index')) {
            $breadcrumbs[] = ['name' => 'Projects', 'url' => route('projects.index')];
        } elseif ($this->request->routeIs('projects.show') && $project instanceof Project) {
            $breadcrumbs[] = ['name' => 'Projects', 'url' => route('projects.index')];
            $breadcrumbs[] = ['name' => $project->title, 'url' => route('projects.show', $project)];
        } elseif ($this->request->routeIs('podcast.index')) {
            $breadcrumbs[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
        } elseif ($this->request->routeIs('podcast.show') && $podcast instanceof Podcast) {
            $breadcrumbs[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
            $breadcrumbs[] = ['name' => $podcast->name, 'url' => $this->canonicalUrl($seoSource, route('podcast.show', $podcast))];
        } elseif ($this->request->routeIs('podcast.episode') && $podcast instanceof Podcast && $episode instanceof Episode) {
            $breadcrumbs[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
            $breadcrumbs[] = ['name' => $podcast->name, 'url' => route('podcast.show', $podcast)];
            $breadcrumbs[] = ['name' => $episode->title, 'url' => route('podcast.episode', [$podcast, $episode])];
        } elseif ($this->request->routeIs('about')) {
            $breadcrumbs[] = ['name' => 'About', 'url' => route('about')];
        } elseif ($this->request->routeIs('contact')) {
            $breadcrumbs[] = ['name' => 'Contact', 'url' => route('contact')];
        } elseif ($this->request->routeIs('privacy')) {
            $breadcrumbs[] = ['name' => 'Privacy', 'url' => route('privacy')];
        } elseif ($this->request->routeIs('uses')) {
            $breadcrumbs[] = ['name' => 'Uses', 'url' => route('uses')];
        } elseif ($this->request->routeIs('archive.index')) {
            $breadcrumbs[] = ['name' => 'Archive', 'url' => $this->canonicalUrl($seoSource, route('archive.index'))];
        }

        if (count($breadcrumbs) < 2) {
            return;
        }

        $schemas[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ],
                $breadcrumbs,
                array_keys($breadcrumbs),
            ),
        ];
    }

    private function canonicalUrl(mixed $seoSource, string $fallback): string
    {
        return $seoSource instanceof SEOData && is_string($seoSource->canonical_url)
            ? $seoSource->canonical_url
            : $fallback;
    }
}
