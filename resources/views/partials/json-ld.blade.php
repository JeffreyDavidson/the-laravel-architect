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

// 2. Article (blog.show)
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

// 3. PodcastSeries + PodcastEpisode
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

// 4. CollectionPage (blog.index)
if (request()->routeIs('blog.index')) {
    $schemas[] = [
        '@type' => 'CollectionPage',
        'name' => 'Blog',
        'url' => route('blog.index'),
    ];
}

// 5. BreadcrumbList
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
