<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use App\ViewModels\PodcastIndexViewModel;
use App\ViewModels\PodcastShowViewModel;
use Illuminate\Contracts\View\View;

class PodcastsController extends Controller
{
    public function index(PodcastIndexViewModel $podcastIndexViewModel): View
    {
        return view('podcast.index', $podcastIndexViewModel->data());
    }

    public function show(Podcast $podcast, PodcastShowViewModel $podcastShowViewModel): View
    {
        abort_unless($podcast->is_active, 404);

        return view('podcast.show', $podcastShowViewModel->data($podcast));
    }
}
