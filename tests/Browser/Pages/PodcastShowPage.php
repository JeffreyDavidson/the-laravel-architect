<?php

namespace Tests\Browser\Pages;

use App\Models\Podcast;
use Pest\Browser\Api\AwaitableWebpage;

final class PodcastShowPage
{
    public static function visit(Podcast $podcast): AwaitableWebpage
    {
        $page = \visit(route('podcast.show', $podcast, absolute: false))->wait(0);

        if (! $page instanceof AwaitableWebpage) {
            throw new \RuntimeException('Expected a browser page.');
        }

        return $page;
    }
}
