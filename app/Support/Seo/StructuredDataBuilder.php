<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Data\StructuredDataPage;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;

final readonly class StructuredDataBuilder
{
    /**
     * Pages with a fixed schema type and breadcrumb name, keyed by route name. Each page's URL
     * is its route. The home page also gets a page schema, but no breadcrumb.
     *
     * @var array<string, array{type: string, name: string}>
     */
    private const array STATIC_PAGES = [
        'about' => ['type' => 'ProfilePage', 'name' => 'About'],
        'contact.create' => ['type' => 'ContactPage', 'name' => 'Contact'],
        'privacy' => ['type' => 'WebPage', 'name' => 'Privacy'],
        'uses' => ['type' => 'WebPage', 'name' => 'Uses'],
    ];

    public function __construct(
        private Request $request,
        private ArticleSchemaBuilder $articles,
        private PodcastSchemaBuilder $podcasts,
        private CollectionSchemaBuilder $collections,
    ) {}

    /**
     * @param  array<string, mixed>  $pageData
     * @return list<array<string, mixed>>
     */
    public function build(array $pageData, ?string $routeName = null): array
    {
        $page = StructuredDataPage::fromViewData(
            $pageData,
            $routeName ?? $this->request->route()
                ?->getName() ?? '',
        );
        $siteUrl = route('home');
        $authorUrl = route('about');
        $schemas = [[
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
        ]];

        $this->addStaticPageSchema($schemas, $page->routeName, $siteUrl, $authorUrl);
        $this->articles->add($schemas, $page, $authorUrl);
        $this->podcasts->add($schemas, $page, $authorUrl);
        $this->addProjectSchema($schemas, $page, $authorUrl);
        $this->collections->add($schemas, $page);
        $this->addBreadcrumbSchema($schemas, $page, $siteUrl);

        return $schemas;
    }

    /**
     * @param  list<array<string, mixed>>  $schemas
     */
    private function addStaticPageSchema(array &$schemas, string $routeName, string $siteUrl, string $authorUrl): void
    {
        $staticPage = $routeName === 'home'
            ? ['type' => 'WebPage', 'name' => 'The Laravel Architect']
            : self::STATIC_PAGES[$routeName] ?? null;

        if ($staticPage === null) {
            return;
        }

        $pageUrl = route($routeName);
        $pageSchema = [
            '@type' => $staticPage['type'],
            '@id' => "{$pageUrl}#page",
            'name' => $staticPage['name'],
            'url' => $pageUrl,
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => "{$siteUrl}#website",
            ],
        ];

        if ($routeName === 'about') {
            $pageSchema['mainEntity'] = [
                '@type' => 'Person',
                '@id' => "{$authorUrl}#person",
            ];
        }

        $schemas[] = $pageSchema;
    }

    /**
     * @param  list<array<string, mixed>>  $schemas
     */
    private function addProjectSchema(array &$schemas, StructuredDataPage $page, string $authorUrl): void
    {
        $project = $page->project;

        if ($page->routeName !== 'projects.show' || ! $project instanceof Project) {
            return;
        }

        $projectUrl = route('projects.show', $project);
        $projectCaseStudy = [
            '@type' => 'CreativeWork',
            '@id' => "{$projectUrl}#project",
            'name' => $project->title,
            'url' => $projectUrl,
            'mainEntityOfPage' => $projectUrl,
            'description' => $project->description,
            'author' => [
                '@type' => 'Person',
                '@id' => "{$authorUrl}#person",
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
     */
    private function addBreadcrumbSchema(array &$schemas, StructuredDataPage $page, string $siteUrl): void
    {
        $trail = $this->breadcrumbTrail($page);

        if ($trail === []) {
            return;
        }

        $breadcrumbs = [['name' => 'Home', 'url' => $siteUrl], ...$trail];
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

    /**
     * The breadcrumbs that follow Home, or none when the page has no breadcrumb trail.
     *
     * @return list<array{name: string, url: string}>
     */
    private function breadcrumbTrail(StructuredDataPage $page): array
    {
        $routeName = $page->routeName;
        $blog = ['name' => 'Blog', 'url' => route('blog.index')];
        $projects = ['name' => 'Projects', 'url' => route('projects.index')];
        $podcasts = ['name' => 'Podcast', 'url' => route('podcast.index')];

        return match (true) {
            $routeName === 'blog.index' => [['name' => 'Blog', 'url' => $page->canonicalUrl(route('blog.index'))]],
            $routeName === 'blog.show' && $page->post instanceof Post => [
                $blog,
                ['name' => $page->post->title, 'url' => route('blog.show', $page->post)],
            ],
            $routeName === 'blog.category' && $page->category instanceof Category => [
                $blog,
                ['name' => $page->category->name, 'url' => $page->canonicalUrl(route('blog.category', $page->category))],
            ],
            $routeName === 'blog.tag' && $page->tag instanceof Tag => [
                $blog,
                ['name' => $page->tag->name, 'url' => $page->canonicalUrl(route('blog.tag', $page->tag))],
            ],
            $routeName === 'projects.index' => [$projects],
            $routeName === 'projects.show' && $page->project instanceof Project => [
                $projects,
                ['name' => $page->project->title, 'url' => route('projects.show', $page->project)],
            ],
            $routeName === 'podcast.index' => [$podcasts],
            $routeName === 'podcast.show' && $page->podcast instanceof Podcast => [
                $podcasts,
                ['name' => $page->podcast->name, 'url' => $page->canonicalUrl(route('podcast.show', $page->podcast))],
            ],
            $routeName === 'podcast.episode' && $page->podcast instanceof Podcast && $page->episode instanceof Episode => [
                $podcasts,
                ['name' => $page->podcast->name, 'url' => route('podcast.show', $page->podcast)],
                ['name' => $page->episode->title, 'url' => route('podcast.episode', [$page->podcast, $page->episode])],
            ],
            $routeName === 'archive.index' => [['name' => 'Archive', 'url' => $page->canonicalUrl(route('archive.index'))]],
            isset(self::STATIC_PAGES[$routeName]) => [['name' => self::STATIC_PAGES[$routeName]['name'], 'url' => route($routeName)]],
            default => [],
        };
    }
}
