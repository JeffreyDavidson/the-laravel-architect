<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PodcastController extends Controller
{
    public function index(): View
    {
        $podcast = Podcast::active()
            ->withCount('publishedEpisodes')
            ->orderBy('sort_order')
            ->first();

        $seoSource = new SEOData(
            title: 'Podcast',
            description: 'Coffee with The Laravel Architect from Jeffrey Davidson — deep dives into Laravel, PHP, architecture patterns, and the craft of building modern web applications.',
        );

        return view('podcast.index', compact('podcast', 'seoSource'));
    }

    public function show(Podcast $podcast): View
    {
        abort_unless($podcast->is_active, 404);

        $episodes = $podcast->publishedEpisodes()
            ->with('tags')
            ->latest('published_at')
            ->paginate(20);

        $latestEpisode = $episodes->first();
        $seoSource = $podcast;

        return view('podcast.show', compact('podcast', 'episodes', 'latestEpisode', 'seoSource'));
    }

    public function episode(Podcast $podcast, Episode $episode): View
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
