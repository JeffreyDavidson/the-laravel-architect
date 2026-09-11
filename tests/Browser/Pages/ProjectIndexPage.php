<?php

namespace Tests\Browser\Pages;

use Pest\Browser\Api\AwaitableWebpage;

final class ProjectIndexPage
{
    public static function visit(): AwaitableWebpage
    {
        $page = \visit(route('projects.index', absolute: false))->wait(0);

        if (! $page instanceof AwaitableWebpage) {
            throw new \RuntimeException('Expected a browser page.');
        }

        return $page;
    }
}
