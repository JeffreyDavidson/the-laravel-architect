<?php

namespace Tests\Browser\Pages;

use App\Models\Post;
use Pest\Browser\Api\AwaitableWebpage;

final class BlogPostPage
{
    public static function visit(Post $post): AwaitableWebpage
    {
        $page = \visit(route('blog.show', $post, absolute: false))->wait(0);

        if (! $page instanceof AwaitableWebpage) {
            throw new \RuntimeException('Expected a browser page.');
        }

        return $page;
    }
}
