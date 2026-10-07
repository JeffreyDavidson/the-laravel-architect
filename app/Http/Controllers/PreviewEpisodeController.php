<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\EpisodeShowViewModel;
use Illuminate\Contracts\View\View;

class PreviewEpisodeController
{
    public function __invoke(Episode $episode, EpisodeShowViewModel $viewModel): View
    {
        $podcast = $episode->podcast;

        abort_unless($podcast instanceof Podcast, 404);

        return view('pages.podcast.episode', $viewModel->previewData($podcast, $episode));
    }
}
