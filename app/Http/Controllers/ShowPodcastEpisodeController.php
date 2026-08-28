<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Contracts\View\View;

class ShowPodcastEpisodeController extends Controller
{
    public function __invoke(Podcast $podcast, Episode $episode): View
    {
        abort_unless($podcast->is_active, 404);
        abort_unless($episode->isPublished(), 404);
        abort_unless($episode->podcast_id === $podcast->id, 404);

        $episode->load(['podcast', 'tags']);

        $nextEpisode = $podcast->publishedEpisodes()
            ->where('published_at', '>', $episode->published_at)
            ->oldest('published_at')
            ->first();

        $prevEpisode = $podcast->publishedEpisodes()
            ->where('published_at', '<', $episode->published_at)
            ->latest('published_at')
            ->first();
        $seoSource = $episode;

        return view('podcast.episode', compact('podcast', 'episode', 'nextEpisode', 'prevEpisode', 'seoSource'));
    }
}
