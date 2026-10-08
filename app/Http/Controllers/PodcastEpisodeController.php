<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\EpisodeShowViewModel;
use Illuminate\Contracts\View\View;

final class PodcastEpisodeController
{
    public function __invoke(
        Podcast $podcast,
        Episode $episode,
        EpisodeShowViewModel $viewModel,
    ): View {
        abort_unless($podcast->is_active, 404);
        abort_unless($episode->isPublished(), 404);

        return view('pages.podcast.episode', $viewModel->data($podcast, $episode));
    }
}
