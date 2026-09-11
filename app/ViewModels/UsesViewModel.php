<?php

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

class UsesViewModel
{
    /** @return array{seoSource: SEOData} */
    public function data(): array
    {
        return [
            'seoSource' => new SEOData(
                title: 'Uses',
                description: 'The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.',
            ),
        ];
    }
}
