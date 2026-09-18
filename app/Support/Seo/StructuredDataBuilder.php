<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Date;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class StructuredDataBuilder
{
    public function __construct(private Request $request) {}

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
        $post = $this->post($pageData);

        if (! $this->request->routeIs('blog.show') || ! $post instanceof Post) {
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
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    private function addPodcastSchemas(array &$schemas, array $pageData, string $authorUrl): void
    {
        $podcast = $this->podcast($pageData);
        $episode = $this->episode($pageData);

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
            $podcastEpisode['datePublished'] = Date::parse($episode->published_at)->toIso8601String();
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
        $project = $this->project($pageData);

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

        if (is_array($project->tech_stack)) {
            $technologyNames = array_values(array_filter(
                $project->tech_stack,
                is_string(...),
            ));

            if ($technologyNames !== []) {
                $projectCaseStudy['keywords'] = implode(', ', $technologyNames);
            }
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
        $seoSource = $this->seoSource($pageData);
        $collectionPage = null;
        $collectionItems = [];
        $positionOffset = 0;

        if ($this->request->routeIs('blog.index') && ($posts = $this->posts($pageData)) instanceof LengthAwarePaginator) {
            $collectionPage = [
                'name' => ($selectedCategory = $this->category($pageData, 'selectedCategory')) instanceof Category
                    ? $selectedCategory->name.' Articles'
                    : 'Blog',
                'url' => $this->canonicalUrl($seoSource, route('blog.index')),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($this->request->routeIs('blog.category')
            && ($posts = $this->posts($pageData)) instanceof LengthAwarePaginator
            && ($category = $this->category($pageData)) instanceof Category) {
            $collectionPage = [
                'name' => $category->name.' Articles',
                'url' => $this->canonicalUrl($seoSource, route('blog.category', $category)),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($this->request->routeIs('blog.tag')
            && ($posts = $this->posts($pageData)) instanceof LengthAwarePaginator
            && ($tag = $this->tag($pageData)) instanceof Tag) {
            $collectionPage = [
                'name' => $tag->name.' Articles',
                'url' => $this->canonicalUrl($seoSource, route('blog.tag', $tag)),
            ];
            $positionOffset = ($posts->currentPage() - 1) * $posts->perPage();

            foreach ($posts as $post) {
                $collectionItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
            }
        } elseif ($this->request->routeIs('projects.index') && ($projects = $this->projects($pageData)) instanceof EloquentCollection) {
            $collectionPage = ['name' => 'Projects', 'url' => route('projects.index')];

            foreach ($projects as $project) {
                $collectionItems[] = ['name' => $project->title, 'url' => route('projects.show', $project)];
            }
        } elseif ($this->request->routeIs('podcast.show')
            && ($podcast = $this->podcast($pageData)) instanceof Podcast
            && ($episodes = $this->episodes($pageData)) instanceof LengthAwarePaginator) {
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
            $podcast = $this->podcast($pageData);

            if ($podcast instanceof Podcast) {
                $collectionItems[] = ['name' => $podcast->name, 'url' => route('podcast.show', $podcast)];
            }
        } elseif ($this->request->routeIs('archive.index') && ($items = $this->items($pageData)) instanceof LengthAwarePaginator) {
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
        $seoSource = $this->seoSource($pageData);
        $post = $this->post($pageData);
        $category = $this->category($pageData);
        $tag = $this->tag($pageData);
        $project = $this->project($pageData);
        $podcast = $this->podcast($pageData);
        $episode = $this->episode($pageData);
        $breadcrumbs = [['name' => 'Home', 'url' => $siteUrl]];

        if ($this->request->routeIs('blog.index')) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => $this->canonicalUrl($seoSource, route('blog.index'))];
        } elseif ($this->request->routeIs('blog.show') && $post instanceof Post) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => route('blog.index')];
            $breadcrumbs[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
        } elseif ($this->request->routeIs('blog.category') && $category instanceof Category) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => route('blog.index')];
            $breadcrumbs[] = ['name' => $category->name, 'url' => $this->canonicalUrl($seoSource, route('blog.category', $category))];
        } elseif ($this->request->routeIs('blog.tag') && $tag instanceof Tag) {
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

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function seoSource(array $pageData): ?SEOData
    {
        $value = $pageData['seoSource'] ?? null;

        return $value instanceof SEOData ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function post(array $pageData): ?Post
    {
        $value = $pageData['post'] ?? null;

        return $value instanceof Post ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function podcast(array $pageData): ?Podcast
    {
        $value = $pageData['podcast'] ?? null;

        return $value instanceof Podcast ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function episode(array $pageData): ?Episode
    {
        $value = $pageData['episode'] ?? null;

        return $value instanceof Episode ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function project(array $pageData): ?Project
    {
        $value = $pageData['project'] ?? null;

        return $value instanceof Project ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function category(array $pageData, string $key = 'category'): ?Category
    {
        $value = $pageData[$key] ?? null;

        return $value instanceof Category ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function tag(array $pageData): ?Tag
    {
        $value = $pageData['tag'] ?? null;

        return $value instanceof Tag ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     * @return LengthAwarePaginator<int, Post>|null
     */
    private function posts(array $pageData): ?LengthAwarePaginator
    {
        $value = $pageData['posts'] ?? null;

        return $value instanceof LengthAwarePaginator ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     * @return EloquentCollection<int, Project>|null
     */
    private function projects(array $pageData): ?EloquentCollection
    {
        $value = $pageData['projects'] ?? null;

        return $value instanceof EloquentCollection ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     * @return LengthAwarePaginator<int, Episode>|null
     */
    private function episodes(array $pageData): ?LengthAwarePaginator
    {
        $value = $pageData['episodes'] ?? null;

        return $value instanceof LengthAwarePaginator ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $pageData
     * @return LengthAwarePaginator<int, array{title: string, url: string}>|null
     */
    private function items(array $pageData): ?LengthAwarePaginator
    {
        $value = $pageData['items'] ?? null;

        return $value instanceof LengthAwarePaginator ? $value : null;
    }
}
