<?php

namespace App\Support\Content;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Facades\URL;

final class PreviewUrlGenerator
{
    public function for(Post|Project|Episode|NewsletterIssue $content): string
    {
        return match (true) {
            $content instanceof Post => URL::temporarySignedRoute(
                'preview.post',
                now()->addHours(2),
                ['post' => $content],
            ),
            $content instanceof Project => URL::temporarySignedRoute(
                'preview.project',
                now()->addHours(2),
                ['project' => $content],
            ),
            $content instanceof Episode => URL::temporarySignedRoute(
                'preview.episode',
                now()->addHours(2),
                ['episode' => $content],
            ),
            $content instanceof NewsletterIssue => URL::temporarySignedRoute(
                'preview.newsletter-issue',
                now()->addHours(2),
                ['newsletterIssue' => $content],
            ),
        };
    }
}
