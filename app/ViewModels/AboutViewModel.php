<?php

declare(strict_types=1);

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

class AboutViewModel
{
    /**
     * @return array{
     *     timeline: list<array{year: string, title: string, desc: string}>,
     *     stats: list<array{label: string, value: string}>,
     *     seoSource: SEOData,
     * }
     */
    public function data(): array
    {
        return [
            'timeline' => [
                ['year' => '~2008', 'title' => 'Started writing PHP', 'desc' => 'Self-taught, building things for fun'],
                ['year' => '2012', 'title' => 'Full Sail University', 'desc' => 'B.S. in Web Design & Development'],
                ['year' => '2014', 'title' => 'Discovered Laravel 4.2', 'desc' => 'Everything clicked'],
                ['year' => '2015', 'title' => 'Moved to Florida', 'desc' => 'Packed up Kansas, headed south'],
                ['year' => '2017', 'title' => 'Daughter Viola born', 'desc' => 'Changed everything'],
                ['year' => '2026', 'title' => 'The Laravel Architect', 'desc' => 'Blog, podcast, YouTube. Building in public'],
            ],
            'stats' => [
                ['label' => 'PHP', 'value' => config()->string('public-site.technology.php')],
                ['label' => 'Laravel', 'value' => (string) config()->integer('public-site.technology.laravel')],
                ['label' => 'Stack', 'value' => 'TALL'],
                ['label' => 'Role', 'value' => 'Sr. Software Eng'],
                ['label' => 'Works', 'value' => 'Remote'],
                ['label' => 'Call Me When', 'value' => 'It\'s Broken'],
            ],
            'seoSource' => new SEOData(
                title: 'About',
                description: 'Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.',
            ),
        ];
    }
}
