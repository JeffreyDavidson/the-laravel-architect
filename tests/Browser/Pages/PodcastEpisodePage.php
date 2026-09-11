<?php

namespace Tests\Browser\Pages;

use App\Models\Episode;
use App\Models\Podcast;
use Pest\Browser\Api\AwaitableWebpage;

final class PodcastEpisodePage
{
    public static function visit(Podcast $podcast, Episode $episode): AwaitableWebpage
    {
        $page = \visit(route('podcast.episode', [$podcast, $episode], absolute: false))->wait(0);

        if (! $page instanceof AwaitableWebpage) {
            throw new \RuntimeException('Expected a browser page.');
        }

        return $page;
    }
}
