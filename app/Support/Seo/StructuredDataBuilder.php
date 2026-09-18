<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class StructuredDataBuilder
{
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
        $routeName ??= $this->request->route()?->getName() ?? '';
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

        $this->addStaticPageSchema($schemas, $siteUrl, $authorUrl, $routeName);
        $this->articles->add($schemas, $pageData, $routeName, $authorUrl);
        $this->podcasts->add($schemas, $pageData, $routeName, $authorUrl);
        $this->addProjectSchema($schemas, $pageData, $authorUrl, $routeName);
        $this->collections->add($schemas, $pageData, $routeName);
        $this->addBreadcrumbSchema($schemas, $pageData, $siteUrl, $routeName);

        return $schemas;
    }

    /**
     * @param  list<array<string, mixed>>  $schemas
     */
    private function addStaticPageSchema(array &$schemas, string $siteUrl, string $authorUrl, string $routeName): void
    {
        $staticPage = match (true) {
            $routeName === 'home' => ['type' => 'WebPage', 'name' => 'The Laravel Architect', 'url' => $siteUrl],
            $routeName === 'about' => ['type' => 'ProfilePage', 'name' => 'About', 'url' => route('about')],
            $routeName === 'contact' => ['type' => 'ContactPage', 'name' => 'Contact', 'url' => route('contact')],
            $routeName === 'privacy' => ['type' => 'WebPage', 'name' => 'Privacy', 'url' => route('privacy')],
            $routeName === 'uses' => ['type' => 'WebPage', 'name' => 'Uses', 'url' => route('uses')],
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

        if ($routeName === 'about') {
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
    private function addProjectSchema(array &$schemas, array $pageData, string $authorUrl, string $routeName): void
    {
        $project = $this->project($pageData);

        if ($routeName !== 'projects.show' || ! $project instanceof Project) {
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
    private function addBreadcrumbSchema(array &$schemas, array $pageData, string $siteUrl, string $routeName): void
    {
        $seoSource = $this->seoSource($pageData);
        $post = $this->post($pageData);
        $category = $this->category($pageData);
        $tag = $this->tag($pageData);
        $project = $this->project($pageData);
        $podcast = $this->podcast($pageData);
        $episode = $this->episode($pageData);
        $breadcrumbs = [['name' => 'Home', 'url' => $siteUrl]];

        if ($routeName === 'blog.index') {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => $this->canonicalUrl($seoSource, route('blog.index'))];
        } elseif ($routeName === 'blog.show' && $post instanceof Post) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => route('blog.index')];
            $breadcrumbs[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
        } elseif ($routeName === 'blog.category' && $category instanceof Category) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => route('blog.index')];
            $breadcrumbs[] = ['name' => $category->name, 'url' => $this->canonicalUrl($seoSource, route('blog.category', $category))];
        } elseif ($routeName === 'blog.tag' && $tag instanceof Tag) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => route('blog.index')];
            $breadcrumbs[] = ['name' => $tag->name, 'url' => $this->canonicalUrl($seoSource, route('blog.tag', $tag))];
        } elseif ($routeName === 'projects.index') {
            $breadcrumbs[] = ['name' => 'Projects', 'url' => route('projects.index')];
        } elseif ($routeName === 'projects.show' && $project instanceof Project) {
            $breadcrumbs[] = ['name' => 'Projects', 'url' => route('projects.index')];
            $breadcrumbs[] = ['name' => $project->title, 'url' => route('projects.show', $project)];
        } elseif ($routeName === 'podcast.index') {
            $breadcrumbs[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
        } elseif ($routeName === 'podcast.show' && $podcast instanceof Podcast) {
            $breadcrumbs[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
            $breadcrumbs[] = ['name' => $podcast->name, 'url' => $this->canonicalUrl($seoSource, route('podcast.show', $podcast))];
        } elseif ($routeName === 'podcast.episode' && $podcast instanceof Podcast && $episode instanceof Episode) {
            $breadcrumbs[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
            $breadcrumbs[] = ['name' => $podcast->name, 'url' => route('podcast.show', $podcast)];
            $breadcrumbs[] = ['name' => $episode->title, 'url' => route('podcast.episode', [$podcast, $episode])];
        } elseif ($routeName === 'about') {
            $breadcrumbs[] = ['name' => 'About', 'url' => route('about')];
        } elseif ($routeName === 'contact') {
            $breadcrumbs[] = ['name' => 'Contact', 'url' => route('contact')];
        } elseif ($routeName === 'privacy') {
            $breadcrumbs[] = ['name' => 'Privacy', 'url' => route('privacy')];
        } elseif ($routeName === 'uses') {
            $breadcrumbs[] = ['name' => 'Uses', 'url' => route('uses')];
        } elseif ($routeName === 'archive.index') {
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
}
