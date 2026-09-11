<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\PodcastEpisodeViewModel;
use Illuminate\Contracts\View\View;

class PodcastEpisodeController
{
    public function __invoke(
        Podcast $podcast,
        Episode $episode,
        PodcastEpisodeViewModel $viewModel,
    ): View {
        abort_unless($podcast->is_active, 404);
        abort_unless($episode->isPublished(), 404);
        abort_unless($episode->podcast_id === $podcast->id, 404);

        return view('podcast.episode', $viewModel->data($podcast, $episode));
    }
}
