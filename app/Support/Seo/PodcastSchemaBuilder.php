<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Data\StructuredDataPage;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Support\Facades\Date;

final class PodcastSchemaBuilder
{
    /**
     * @param  list<array<string, mixed>>  $schemas
     */
    public function add(array &$schemas, StructuredDataPage $page, string $authorUrl): void
    {
        $routeName = $page->routeName;
        $podcast = $page->podcast;
        $episode = $page->episode;

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

        if ($episode->duration_seconds) {
            $podcastEpisode['duration'] = $this->isoDuration($episode->duration_seconds);
        }

        $schemas[] = $podcastEpisode;
    }

    /** Format seconds as an ISO 8601 duration, for example PT1H2M5S. */
    private function isoDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return "PT{$hours}H{$minutes}M{$remainingSeconds}S";
    }
}
