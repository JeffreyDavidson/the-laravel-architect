<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use App\Queries\EpisodeNavigationQuery;
use Illuminate\Contracts\View\View;

class ShowPodcastEpisodeController extends Controller
{
    public function __invoke(
        Podcast $podcast,
        Episode $episode,
        EpisodeNavigationQuery $episodeNavigationQuery,
    ): View {
        abort_unless($podcast->is_active, 404);
        abort_unless($episode->isPublished(), 404);
        abort_unless($episode->podcast_id === $podcast->id, 404);

        $episode->load(['podcast', 'tags']);

        [
            'previous' => $prevEpisode,
            'next' => $nextEpisode,
        ] = $episodeNavigationQuery->get($podcast, $episode);
        $seoSource = $episode;

        return view('podcast.episode', [
            'podcast' => $podcast,
            'episode' => $episode,
            'nextEpisode' => $nextEpisode,
            'prevEpisode' => $prevEpisode,
            'seoSource' => $seoSource,
        ]);
    }
}
