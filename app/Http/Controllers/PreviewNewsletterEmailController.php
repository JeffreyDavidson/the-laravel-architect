<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterIssue;
use Illuminate\Http\Response;

final class PreviewNewsletterEmailController
{
    public function __invoke(NewsletterIssue $newsletterIssue): Response
    {
        return response(new NewsletterIssueMail($newsletterIssue)->render())
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store, private');
    }
}
