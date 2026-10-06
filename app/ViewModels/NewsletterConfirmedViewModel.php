<?php

declare(strict_types=1);

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

class NewsletterConfirmedViewModel
{
    /** @return array{seoSource: SEOData} */
    public function data(): array
    {
        return [
            'seoSource' => new SEOData(
                title: 'You’re Confirmed',
                description: 'Your subscription to The Laravel Architect newsletter is confirmed.',
            )->markAsNoindex(),
        ];
    }
}
