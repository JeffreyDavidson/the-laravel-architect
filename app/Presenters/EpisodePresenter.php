<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\Episode;
use App\Models\Podcast;
use App\Presenters\Concerns\LinksToPublicPageOrPreview;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Carbon;
use JeffreyDavidson\CreatorKit\Support\Seo\JsonLd;

final readonly class EpisodePresenter
{
    use LinksToPublicPageOrPreview;

    public function __construct(
        private Episode $episode,
        private UrlGenerator $urls,
    ) {}

    public static function from(Episode $episode): self
    {
        return app()->make(self::class, ['episode' => $episode]);
    }

    /** The episode's page on its show, or null while the episode or its show is not public. */
    public function publicUrl(): ?string
    {
        $podcast = $this->episode->podcast;

        if (! $this->episode->isPublished() || ! $podcast instanceof Podcast || ! $podcast->is_active) {
            return null;
        }

        return $this->urls->route('podcasts.episode', [$podcast, $this->episode]);
    }

    public function previewUrl(): string
    {
        return $this->signedPreviewUrl($this->urls, 'preview.episode', ['episode' => $this->episode]);
    }

    /**
     * The episode as a schema.org PodcastEpisode on its show's page, part of the show's series.
     *
     * @param  Podcast  $podcast  The show the episode belongs to.
     * @return array<string, mixed>
     */
    public function episodeSchema(Podcast $podcast): array
    {
        $episodeUrl = $this->urls->route('podcasts.episode', [$podcast, $this->episode]);
        $schema = [
            '@type' => 'PodcastEpisode',
            '@id' => "{$episodeUrl}#episode",
            'name' => $this->episode->title,
            'url' => $episodeUrl,
            'mainEntityOfPage' => $episodeUrl,
            'partOfSeries' => PodcastPresenter::from($podcast)->seriesReference(),
        ];

        if ($this->episode->description) {
            $schema['description'] = $this->episode->description;
        }

        $publishedAt = $this->episode->publishedAt();

        if ($publishedAt instanceof Carbon) {
            $schema['datePublished'] = $publishedAt->toIso8601String();
        }

        if ($this->episode->episode_number !== null) {
            $schema['episodeNumber'] = $this->episode->episode_number;
        }

        if ($this->episode->duration_seconds) {
            $schema['duration'] = JsonLd::isoDuration($this->episode->duration_seconds);
        }

        return $schema;
    }

    public function code(): string
    {
        return 'S'.str_pad((string) $this->episode->season_number, 2, '0', STR_PAD_LEFT)
            .'E'.str_pad((string) ($this->episode->episode_number ?? 0), 2, '0', STR_PAD_LEFT);
    }

    public function duration(): string
    {
        $seconds = $this->episode->duration_seconds;

        if (! $seconds) {
            return '';
        }

        $totalMinutes = max(1, (int) round($seconds / 60));
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes} min";
    }

    /** The Transistor player URL for a valid share URL, or null when the episode has none. */
    public function transistorEmbedUrl(): ?string
    {
        $episodeId = $this->episode->transistorEpisodeId();

        return $episodeId === null ? null : "https://share.transistor.fm/e/{$episodeId}";
    }

    /** Whether the episode links to YouTube, which gives it a video block on its page. */
    public function hasYouTube(): bool
    {
        return is_string($this->episode->youtube_url)
            && str_contains($this->episode->youtube_url, 'youtu');
    }

    /** The video ID from a youtube.com watch or embed link or a youtu.be link, for the embedded player. */
    public function youtubeVideoId(): ?string
    {
        if (! $this->hasYouTube()) {
            return null;
        }

        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', (string) $this->episode->youtube_url, $matches);

        return $matches[1] ?? null;
    }
}
