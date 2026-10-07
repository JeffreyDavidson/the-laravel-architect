<?php

declare(strict_types=1);

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

class ServiceViewModel
{
    /**
     * @return array{
     *     services: list<array{id: string, icon: string, title: string, description: string, examples: list<string>, tools: string}>,
     *     seoSource: SEOData,
     * }
     */
    public function data(): array
    {
        return [
            'services' => [
                ['id' => 'build', 'icon' => 'heroicon-o-squares-2x2', 'title' => 'Build your application', 'description' => 'Turn the way your business works into software that supports it. From a new product to an internal tool, start with the workflows that matter most.', 'examples' => ['Custom Laravel applications and new features', 'Admin panels and tools for everyday operations', 'Data modeling and integrations'], 'tools' => 'Laravel · Filament · Livewire'],
                ['id' => 'improve', 'icon' => 'heroicon-o-wrench-screwdriver', 'title' => 'Improve an existing codebase', 'description' => 'You don’t always need a rewrite. Find what’s slowing your team down, preserve what works, and make focused changes that are easier to maintain.', 'examples' => ['Code reviews and practical technical priorities', 'Laravel and PHP upgrades', 'Refactoring backed by regression tests'], 'tools' => 'PHP · Laravel · Code review'],
                ['id' => 'ship', 'icon' => 'heroicon-o-shield-check', 'title' => 'Ship with confidence', 'description' => 'Make important behavior easier to verify and releases easier to repeat. Build the safety net around the workflows your business depends on.', 'examples' => ['Automated tests for critical user journeys', 'Continuous integration and deployment workflows', 'Production monitoring and error visibility'], 'tools' => 'Pest · Laravel Forge · Production monitoring'],
            ],
            'seoSource' => new SEOData(
                title: 'Services',
                description: 'Laravel development, codebase modernization, and testing with Jeffrey Davidson. Build useful applications and make your next release easier.',
            ),
        ];
    }
}
