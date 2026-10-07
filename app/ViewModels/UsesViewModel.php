<?php

declare(strict_types=1);

namespace App\ViewModels;

use RalphJSmit\Laravel\SEO\Support\SEOData;

final class UsesViewModel
{
    /**
     * Each section feeds the mobile jump links (shortLabel), the sidebar jump links (label) and its
     * own heading. Tool sections list linked items; the site technology section is a grid.
     *
     * @return array{
     *     sections: list<array{
     *         id: string,
     *         heading: string,
     *         label: string,
     *         shortLabel: string,
     *         icon: string,
     *         iconVariant: string,
     *         siteTechnology: bool,
     *         items: list<array{icon: string, name: string, desc: string, tag?: string, url?: string}>,
     *     }>,
     *     seoSource: SEOData,
     * }
     */
    public function data(): array
    {
        return [
            'sections' => $this->sections(),
            'seoSource' => new SEOData(
                title: 'Uses',
                description: 'The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.',
            ),
        ];
    }

    /**
     * @return list<array{
     *     id: string,
     *     heading: string,
     *     label: string,
     *     shortLabel: string,
     *     icon: string,
     *     iconVariant: string,
     *     siteTechnology: bool,
     *     items: list<array{icon: string, name: string, desc: string, tag?: string, url?: string}>,
     * }>
     */
    private function sections(): array
    {
        return [
            [
                'id' => 'hardware',
                'heading' => 'Hardware',
                'label' => 'Hardware',
                'shortLabel' => 'Hardware',
                'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'iconVariant' => 'brand',
                'siteTechnology' => false,
                'items' => [
                    ['icon' => '💻', 'name' => 'MacBook Pro 16" (Nov 2024)', 'desc' => 'Apple M4 Max, 48GB RAM. The daily driver for everything: development, content creation, and life.', 'tag' => 'Laptop'],
                    ['icon' => '🖥️', 'name' => 'LG 39GS95QE', 'desc' => '39" ultrawide OLED gaming monitor. Gorgeous colors, plenty of real estate for code + browser side by side.', 'tag' => 'Monitor', 'url' => 'https://www.lg.com/us/monitors/lg-39gs95qe-b-gaming-monitor'],
                    ['icon' => '⌨️', 'name' => 'Apple Magic Keyboard', 'desc' => 'Simple, reliable, and matches the ecosystem. No mechanical keyboard phase. Yet.', 'tag' => 'Keyboard'],
                    ['icon' => '🖱️', 'name' => 'Apple Magic Trackpad', 'desc' => 'Gestures are too good to give up. The trackpad stays.', 'tag' => 'Trackpad'],
                    ['icon' => '🔌', 'name' => 'CalDigit TS3 Plus', 'desc' => 'Thunderbolt dock. One cable to rule them all. Monitor, peripherals, power, everything.', 'tag' => 'Dock', 'url' => 'https://www.caldigit.com/ts3-plus/'],
                    ['icon' => '🔊', 'name' => 'Kanto YU2', 'desc' => 'Compact powered desktop speakers. Big sound from a small footprint. Perfect for the desk setup.', 'tag' => 'Speakers', 'url' => 'https://www.kantoaudio.com/powered-speakers/yu2/'],
                    ['icon' => '🪑', 'name' => 'Secretlab Chair', 'desc' => 'Comfortable for long coding sessions. Worth the investment.', 'tag' => 'Chair'],
                    ['icon' => '🪵', 'name' => 'Fully Jarvis 72×30', 'desc' => 'Black bamboo standing desk. Sit-stand with plenty of room for the ultrawide and all the gear.', 'tag' => 'Desk', 'url' => 'https://www.fully.com/standing-desks/jarvis.html'],
                ],
            ],
            [
                'id' => 'development',
                'heading' => 'Development',
                'label' => 'Development',
                'shortLabel' => 'Development',
                'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                'iconVariant' => 'brand',
                'siteTechnology' => false,
                'items' => [
                    ['icon' => '📝', 'name' => 'Visual Studio Code', 'desc' => 'My editor of choice. Fast, extensible, and the ecosystem of extensions is unbeatable.', 'tag' => 'Editor', 'url' => 'https://code.visualstudio.com'],
                    ['icon' => '🐚', 'name' => 'Warp', 'desc' => 'Modern terminal with AI built in. Getting a little bloated though. Eyeing Ghostty as a leaner alternative.', 'tag' => 'Terminal', 'url' => 'https://www.warp.dev'],
                    ['icon' => '🦙', 'name' => 'Laravel Herd', 'desc' => 'Local development environment. Zero-config PHP, nginx, and dnsmasq on macOS.', 'tag' => 'Local Dev', 'url' => 'https://herd.laravel.com'],
                    ['icon' => '🔨', 'name' => 'Laravel Forge', 'desc' => 'Server management and deployment. Push to main and it\'s live.', 'tag' => 'Hosting', 'url' => 'https://forge.laravel.com'],
                    ['icon' => '🐙', 'name' => 'GitHub', 'desc' => 'Version control, CI/CD, and open source home.', 'tag' => 'Git', 'url' => 'https://github.com/JeffreyDavidson'],
                    ['icon' => '🗄️', 'name' => 'TablePlus', 'desc' => 'Database GUI. Clean, fast, and works beautifully with MySQL, SQLite, and Redis.', 'tag' => 'Database', 'url' => 'https://tableplus.com'],
                    ['icon' => '🦊', 'name' => 'Firefox', 'desc' => 'Primary browser for development and daily use. Looking at Arc for something fresh.', 'tag' => 'Browser', 'url' => 'https://www.mozilla.org/firefox/'],
                    ['icon' => '🔦', 'name' => 'Ray', 'desc' => 'By Spatie. A beautiful debugging tool that replaced dd() in my workflow.', 'tag' => 'Debugging', 'url' => 'https://myray.app'],
                    ['icon' => '🧪', 'name' => 'Pest', 'desc' => 'Testing framework for PHP. Elegant syntax, powerful assertions. Three suites: Feature, Integration, Unit.', 'tag' => 'Testing', 'url' => 'https://pestphp.com'],
                ],
            ],
            [
                'id' => 'content-creation',
                'heading' => 'Content Creation',
                'label' => 'Content Creation',
                'shortLabel' => 'Content',
                'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                'iconVariant' => 'accent',
                'siteTechnology' => false,
                'items' => [
                    ['icon' => '🎙️', 'name' => 'Shure SM7B', 'desc' => 'The industry standard broadcast mic. Warm, rich sound that makes everything sound professional.', 'tag' => 'Microphone', 'url' => 'https://www.shure.com/en-US/products/microphones/sm/sm7b'],
                    ['icon' => '🎛️', 'name' => 'RØDECaster Pro', 'desc' => 'All-in-one podcast production studio. Handles audio processing, mixing, and recording in one box.', 'tag' => 'Audio', 'url' => 'https://rode.com/en/interfaces-mixers/rodecaster-series/rodecaster-pro'],
                    ['icon' => '📷', 'name' => 'Sony ZV-E10', 'desc' => 'Mirrorless camera made for content creators. Great video quality, compact body, interchangeable lenses.', 'tag' => 'Camera', 'url' => 'https://electronics.sony.com/imaging/interchangeable-lens-cameras/aps-c/p/ilczve10-b'],
                    ['icon' => '💡', 'name' => 'Elgato Key Light', 'desc' => 'Edge-lit LED panel. App-controlled brightness and color temperature. Clean, even lighting for video.', 'tag' => 'Lighting', 'url' => 'https://www.elgato.com/us/en/p/key-light'],
                    ['icon' => '🎮', 'name' => 'Elgato Stream Deck XL', 'desc' => '32 programmable LCD keys. Scene switching, shortcuts, and macros for streaming and productivity.', 'tag' => 'Control', 'url' => 'https://www.elgato.com/us/en/p/stream-deck-xl'],
                    ['icon' => '🕹️', 'name' => 'Elgato Stream Deck MK.2', 'desc' => '15-key companion to the XL. Extra controls for when one deck isn\'t enough.', 'tag' => 'Control', 'url' => 'https://www.elgato.com/us/en/p/stream-deck-mk2-black'],
                ],
            ],
            [
                'id' => 'productivity',
                'heading' => 'Productivity',
                'label' => 'Productivity',
                'shortLabel' => 'Productivity',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'iconVariant' => 'brand',
                'siteTechnology' => false,
                'items' => [
                    ['icon' => '📓', 'name' => 'Notion', 'desc' => 'Everything lives here. Family organization, project planning, content calendars, and notes.', 'tag' => 'Notes', 'url' => 'https://www.notion.so'],
                    ['icon' => '💬', 'name' => 'Slack', 'desc' => 'Work communication and Laravel community channels.', 'tag' => 'Chat', 'url' => 'https://slack.com'],
                    ['icon' => '🎮', 'name' => 'Discord', 'desc' => 'Dev communities, podcast listeners, and gaming.', 'tag' => 'Community', 'url' => 'https://discord.com'],
                ],
            ],
            [
                'id' => 'this-site',
                'heading' => 'This Site Is Built With',
                'label' => 'This Site',
                'shortLabel' => 'This site',
                'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                'iconVariant' => 'brand',
                'siteTechnology' => true,
                'items' => [
                    ['icon' => '🐘', 'name' => 'Laravel '.config()->integer('public-site.technology.laravel'), 'desc' => 'Framework'],
                    ['icon' => '🛡️', 'name' => 'Filament '.config()->integer('public-site.technology.filament'), 'desc' => 'Admin panel'],
                    ['icon' => '🎨', 'name' => 'Tailwind CSS', 'desc' => 'Styling'],
                    ['icon' => '📄', 'name' => 'Blade', 'desc' => 'Templates'],
                    ['icon' => '💾', 'name' => 'SQLite', 'desc' => 'Database'],
                    ['icon' => '🧪', 'name' => 'Pest', 'desc' => 'Testing'],
                    ['icon' => '🔨', 'name' => 'Laravel Forge', 'desc' => 'Deployment'],
                    ['icon' => '🖼️', 'name' => 'Intervention Image', 'desc' => 'OG images'],
                    ['icon' => '✨', 'name' => 'Prism.js', 'desc' => 'Syntax highlighting'],
                    ['icon' => '📧', 'name' => 'Resend', 'desc' => 'Email'],
                    ['icon' => '⛰️', 'name' => 'Alpine.js', 'desc' => 'Interactivity'],
                ],
            ],
        ];
    }
}
