<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;

class PodcastController extends Controller
{
    public function index()
    {
        $podcasts = Podcast::active()
            ->withCount('publishedEpisodes')
            ->orderBy('sort_order')
            ->get();

        return view('podcast.index', compact('podcasts'));
    }

    public function show(Podcast $podcast)
    {
        $episodes = $podcast->publishedEpisodes()
            ->with('tags')
            ->latest('published_at')
            ->paginate(20);

        $latestEpisode = $episodes->first();

        return view('podcast.show', compact('podcast', 'episodes', 'latestEpisode'));
    }

    public function episode(Podcast $podcast, Episode $episode)
    {
        abort_unless($episode->status === 'published', 404);
        abort_unless($episode->podcast_id === $podcast->id, 404);

        $episode->load('tags');

        $nextEpisode = $podcast->publishedEpisodes()
            ->where('episode_number', '>', $episode->episode_number)
            ->orderBy('episode_number')
            ->first();

        $prevEpisode = $podcast->publishedEpisodes()
            ->where('episode_number', '<', $episode->episode_number)
            ->orderByDesc('episode_number')
            ->first();

        return view('podcast.episode', compact('podcast', 'episode', 'nextEpisode', 'prevEpisode'));
    }
}
