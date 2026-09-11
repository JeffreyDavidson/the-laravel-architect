<?php

namespace Tests\Browser\Pages;

use App\Models\Project;
use Pest\Browser\Api\AwaitableWebpage;

final class ProjectShowPage
{
    public static function visit(Project $project): AwaitableWebpage
    {
        $page = \visit(route('projects.show', $project, absolute: false))->wait(0);

        if (! $page instanceof AwaitableWebpage) {
            throw new \RuntimeException('Expected a browser page.');
        }

        return $page;
    }
}
