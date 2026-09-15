<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\PodcastEpisodeViewModel;
use Illuminate\Contracts\View\View;

class PreviewEpisodeController
{
    public function __invoke(Episode $episode, PodcastEpisodeViewModel $viewModel): View
    {
        $podcast = $episode->podcast;

        abort_unless($podcast instanceof Podcast, 404);

        return view('podcast.episode', $viewModel->data($podcast, $episode, preview: true));
    }
}
