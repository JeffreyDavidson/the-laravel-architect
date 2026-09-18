<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Support\Facades\Date;

final class PodcastSchemaBuilder
{
    /**
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, mixed>  $pageData
     */
    public function add(array &$schemas, array $pageData, string $routeName, string $authorUrl): void
    {
        $podcast = $this->podcast($pageData);
        $episode = $this->episode($pageData);

        if (! in_array($routeName, ['podcast.show', 'podcast.episode'], true) || ! $podcast instanceof Podcast) {
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

        if ($routeName !== 'podcast.episode' || ! $episode instanceof Episode) {
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
}
