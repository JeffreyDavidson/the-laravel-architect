<?php

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

class ServiceViewModel
{
    /** @return array{seoSource: SEOData} */
    public function data(): array
    {
        return [
            'seoSource' => new SEOData(
                title: 'Services',
                description: 'Laravel development, codebase modernization, and testing with Jeffrey Davidson. Build useful applications and make your next release easier.',
            ),
        ];
    }
}
