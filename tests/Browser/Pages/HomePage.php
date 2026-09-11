<?php

namespace Tests\Browser\Pages;

use Pest\Browser\Api\AwaitableWebpage;

final class HomePage
{
    public static function visit(): AwaitableWebpage
    {
        $page = \visit(route('home', absolute: false))->wait(0);

        if (! $page instanceof AwaitableWebpage) {
            throw new \RuntimeException('Expected a browser page.');
        }

        return $page;
    }
}
