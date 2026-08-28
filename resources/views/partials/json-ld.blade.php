@php
$siteUrl = route('home');
$siteName = 'The Laravel Architect';
$authorName = 'Jeffrey Davidson';
$authorUrl = route('about');

$schemas = [];

// 1. WebSite + Person (every page)
$schemas[] = [
    '@type' => 'WebSite',
    '@id' => $siteUrl.'#website',
    'name' => $siteName,
    'url' => $siteUrl,
    'author' => [
        '@type' => 'Person',
        '@id' => $authorUrl.'#person',
        'name' => $authorName,
        'url' => $authorUrl,
    ],
];

// 2. Static public pages
$staticPage = null;

if (request()->routeIs('home')) {
    $staticPage = ['type' => 'WebPage', 'name' => $siteName, 'url' => $siteUrl];
} elseif (request()->routeIs('about')) {
    $staticPage = ['type' => 'ProfilePage', 'name' => 'About', 'url' => route('about')];
} elseif (request()->routeIs('contact')) {
    $staticPage = ['type' => 'ContactPage', 'name' => 'Contact', 'url' => route('contact')];
} elseif (request()->routeIs('privacy')) {
    $staticPage = ['type' => 'WebPage', 'name' => 'Privacy', 'url' => route('privacy')];
} elseif (request()->routeIs('uses')) {
    $staticPage = ['type' => 'WebPage', 'name' => 'Uses', 'url' => route('uses')];
}

if ($staticPage) {
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

    if (request()->routeIs('about')) {
        $pageSchema['mainEntity'] = [
            '@type' => 'Person',
            '@id' => $authorUrl.'#person',
        ];
    }

    $schemas[] = $pageSchema;
}

// 3. Article (blog.show)
if (request()->routeIs('blog.show') && isset($post)) {
    $article = [
        '@type' => 'Article',
        '@id' => route('blog.show', $post).'#article',
        'url' => route('blog.show', $post),
        'headline' => $post->title,
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified' => $post->updated_at?->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            '@id' => $authorUrl.'#person',
        ],
        'mainEntityOfPage' => route('blog.show', $post),
        'description' => $post->excerpt ?? '',
    ];
    $featuredImage = $post->featured_image_url;
    if ($featuredImage) {
        $article['image'] = $featuredImage;
    }
    $schemas[] = $article;
}

// 4. PodcastSeries + PodcastEpisode
if ((request()->routeIs('podcast.show') || request()->routeIs('podcast.episode')) && isset($podcast)) {
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

    $coverImage = $podcast->cover_image_url;
    if ($coverImage) {
        $podcastSeries['image'] = $coverImage;
    }

    $schemas[] = $podcastSeries;

    if (request()->routeIs('podcast.episode') && isset($episode)) {
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

        if ($episode->audio_url) {
            $podcastEpisode['associatedMedia'] = [
                '@type' => 'MediaObject',
                'contentUrl' => $episode->audio_url,
            ];
        }

        $schemas[] = $podcastEpisode;
    }
}

// 5. CreativeWork (projects.show)
if (request()->routeIs('projects.show') && isset($project)) {
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

    $relatedUrls = array_values(array_filter([
        $project->url,
        $project->github_url,
    ]));

    if ($relatedUrls !== []) {
        $projectCaseStudy['sameAs'] = $relatedUrls;
    }

    $schemas[] = $projectCaseStudy;
}

// 6. CollectionPage + ItemList (public indexes)
$collectionPage = null;
$collectionItems = [];
$collectionPositionOffset = 0;

if (request()->routeIs('blog.index') && isset($posts)) {
    $collectionPage = ['name' => 'Blog', 'url' => route('blog.index')];

    foreach ($posts as $collectionPost) {
        $collectionItems[] = [
            'name' => $collectionPost->title,
            'url' => route('blog.show', $collectionPost),
        ];
    }
} elseif (request()->routeIs('blog.category') && isset($category, $posts)) {
    $collectionPage = [
        'name' => $category->name.' Articles',
        'url' => $seoSource->canonical_url,
    ];
    $collectionPositionOffset = ($posts->currentPage() - 1) * $posts->perPage();

    foreach ($posts as $collectionPost) {
        $collectionItems[] = [
            'name' => $collectionPost->title,
            'url' => route('blog.show', $collectionPost),
        ];
    }
} elseif (request()->routeIs('blog.tag') && isset($tag, $posts)) {
    $collectionPage = [
        'name' => $tag->name.' Articles',
        'url' => $seoSource->canonical_url,
    ];
    $collectionPositionOffset = ($posts->currentPage() - 1) * $posts->perPage();

    foreach ($posts as $collectionPost) {
        $collectionItems[] = [
            'name' => $collectionPost->title,
            'url' => route('blog.show', $collectionPost),
        ];
    }
} elseif (request()->routeIs('projects.index') && isset($projects)) {
    $collectionPage = ['name' => 'Projects', 'url' => route('projects.index')];

    foreach ($projects as $collectionProject) {
        $collectionItems[] = [
            'name' => $collectionProject->title,
            'url' => route('projects.show', $collectionProject),
        ];
    }
} elseif (request()->routeIs('podcast.show') && isset($podcast, $episodes)) {
    $collectionPage = [
        'name' => $podcast->name.' Episodes',
        'url' => $seoSource->canonical_url,
    ];
    $collectionPositionOffset = ($episodes->currentPage() - 1) * $episodes->perPage();

    foreach ($episodes as $collectionEpisode) {
        $collectionItems[] = [
            'name' => $collectionEpisode->title,
            'url' => route('podcast.episode', [$podcast, $collectionEpisode]),
        ];
    }
} elseif (request()->routeIs('podcast.index')) {
    $collectionPage = ['name' => 'Podcast', 'url' => route('podcast.index')];

    if (isset($podcast) && $podcast) {
        $collectionItems[] = [
            'name' => $podcast->name,
            'url' => route('podcast.show', $podcast),
        ];
    }
}

if ($collectionPage) {
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
            'position' => $collectionPositionOffset + $index + 1,
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

// 7. BreadcrumbList
$breadcrumbItems = [];
$breadcrumbItems[] = ['name' => 'Home', 'url' => $siteUrl];

if (request()->routeIs('blog.index')) {
    $breadcrumbItems[] = ['name' => 'Blog', 'url' => route('blog.index')];
} elseif (request()->routeIs('blog.show') && isset($post)) {
    $breadcrumbItems[] = ['name' => 'Blog', 'url' => route('blog.index')];
    $breadcrumbItems[] = ['name' => $post->title, 'url' => route('blog.show', $post)];
} elseif (request()->routeIs('blog.category') && isset($category)) {
    $breadcrumbItems[] = ['name' => 'Blog', 'url' => route('blog.index')];
    $breadcrumbItems[] = ['name' => $category->name, 'url' => route('blog.category', $category)];
} elseif (request()->routeIs('blog.tag') && isset($tag)) {
    $breadcrumbItems[] = ['name' => 'Blog', 'url' => route('blog.index')];
    $breadcrumbItems[] = ['name' => $tag->name, 'url' => route('blog.tag', $tag)];
} elseif (request()->routeIs('projects.index')) {
    $breadcrumbItems[] = ['name' => 'Projects', 'url' => route('projects.index')];
} elseif (request()->routeIs('projects.show') && isset($project)) {
    $breadcrumbItems[] = ['name' => 'Projects', 'url' => route('projects.index')];
    $breadcrumbItems[] = ['name' => $project->title, 'url' => route('projects.show', $project)];
} elseif (request()->routeIs('podcast.index')) {
    $breadcrumbItems[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
} elseif (request()->routeIs('podcast.show') && isset($podcast)) {
    $breadcrumbItems[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
    $breadcrumbItems[] = ['name' => $podcast->name, 'url' => route('podcast.show', $podcast)];
} elseif (request()->routeIs('podcast.episode') && isset($podcast) && isset($episode)) {
    $breadcrumbItems[] = ['name' => 'Podcast', 'url' => route('podcast.index')];
    $breadcrumbItems[] = ['name' => $podcast->name, 'url' => route('podcast.show', $podcast)];
    $breadcrumbItems[] = ['name' => $episode->title, 'url' => route('podcast.episode', [$podcast, $episode])];
} elseif (request()->routeIs('about')) {
    $breadcrumbItems[] = ['name' => 'About', 'url' => route('about')];
} elseif (request()->routeIs('contact')) {
    $breadcrumbItems[] = ['name' => 'Contact', 'url' => route('contact')];
} elseif (request()->routeIs('privacy')) {
    $breadcrumbItems[] = ['name' => 'Privacy', 'url' => route('privacy')];
} elseif (request()->routeIs('uses')) {
    $breadcrumbItems[] = ['name' => 'Uses', 'url' => route('uses')];
}

if (count($breadcrumbItems) > 1) {
    $listItems = [];
    foreach ($breadcrumbItems as $i => $item) {
        $listItems[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => $item['url'],
        ];
    }
    $schemas[] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => $listItems,
    ];
}

$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => $schemas,
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
