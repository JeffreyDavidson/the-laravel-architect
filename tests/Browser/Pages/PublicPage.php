<?php

namespace Tests\Browser\Pages;

use Pest\Browser\Api\AwaitableWebpage;

abstract class PublicPage
{
    protected const ROUTE = '';

    public static function visit(): AwaitableWebpage
    {
        $page = \visit(route(static::ROUTE, absolute: false))->wait(0);

        if (! $page instanceof AwaitableWebpage) {
            throw new \RuntimeException('Expected a browser page.');
        }

        return $page;
    }
}
