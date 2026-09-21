<?php

declare(strict_types=1);

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

class PrivacyViewModel
{
    /** @return array{seoSource: SEOData} */
    public function data(): array
    {
        return [
            'seoSource' => new SEOData(
                title: 'Privacy',
                description: 'How The Laravel Architect handles contact messages, newsletter subscriptions, observability, and essential site data.',
            ),
        ];
    }
}
