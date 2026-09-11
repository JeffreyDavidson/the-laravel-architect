<?php

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

class AboutViewModel
{
    /** @return array{seoSource: SEOData} */
    public function data(): array
    {
        return [
            'seoSource' => new SEOData(
                title: 'About',
                description: 'Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.',
            ),
        ];
    }
}
