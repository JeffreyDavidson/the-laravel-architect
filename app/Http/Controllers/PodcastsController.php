<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use App\ViewModels\PodcastShowViewModel;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PodcastsController extends Controller
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

    public function show(Podcast $podcast, PodcastShowViewModel $podcastShowViewModel): View
    {
        abort_unless($podcast->is_active, 404);

        $data = $podcastShowViewModel->data($podcast);

        abort_if($data['episodes']->currentPage() > $data['episodes']->lastPage(), 404);

        return view('podcast.show', $data);
    }
}
