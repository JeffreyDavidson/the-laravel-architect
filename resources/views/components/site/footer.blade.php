@php
    $navigationLinks = [
        ['label' => 'Blog', 'route' => 'blog.index'],
        ['label' => 'Podcast', 'route' => 'podcast.index'],
        ['label' => 'Projects', 'route' => 'projects.index'],
        ['label' => 'About', 'route' => 'about'],
        ['label' => 'Services', 'route' => 'services'],
        ['label' => 'Uses', 'route' => 'uses'],
    ];

    $resourceLinks = [
        ['label' => 'Contact', 'route' => 'contact'],
        ['label' => 'Privacy', 'route' => 'privacy'],
        ['label' => 'RSS Feed', 'url' => '/rss'],
        ['label' => 'Newsletter archive', 'route' => 'newsletter.index'],
        ['label' => 'Full archive', 'route' => 'archive.index'],
        ['label' => 'uses.tech', 'url' => 'https://uses.tech', 'external' => true],
    ];
@endphp

<footer class="dark:bg-brand-950 dark:border-surface-border relative overflow-hidden border-t border-gray-200 bg-gray-50">
    <div class="relative mx-auto max-w-7xl px-4 pt-8 pb-8 sm:px-6 sm:pt-12 lg:px-8">
        <div class="mb-8 flex flex-col justify-between gap-6 lg:mb-14 lg:flex-row lg:gap-10">
            <div class="max-w-sm">
                <a href="{{ route('home') }}" class="group mb-3 flex items-center gap-3">
                    <img
                        src="/images/elephant-companion-128.webp"
                        alt=""
                        width="40"
                        height="40"
                        loading="lazy"
                        decoding="async"
                        class="h-10 w-10 rounded-full"
                    />
                    <span class="group-hover:text-brand-600 flex items-baseline gap-1 text-gray-900 transition-colors dark:text-white">
                        <span class="text-meta font-semibold tracking-widest uppercase">The</span>
                        <span class="font-empera text-xl tracking-wide">Laravel</span>
                        <span class="text-meta font-semibold tracking-widest uppercase">Architect</span>
                    </span>
                </a>
                <p class="mb-4 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    Building elegant web applications with Laravel. Writing about code, architecture, and the developer
                    life.
                </p>
                <x-social-links variant="buttons" />
            </div>

            <div class="flex gap-10 sm:gap-16">
                <x-site.footer-links title="Navigate" :links="$navigationLinks" />
                <x-site.footer-links title="Resources" :links="$resourceLinks" />
            </div>
        </div>

        <div class="dark:bg-brand-800 h-px w-full bg-gray-200"></div>

        <div class="flex flex-col items-center justify-between gap-3 pt-6 sm:flex-row">
            <p class="text-xs text-gray-600 dark:text-gray-400">
                &copy; {{ date('Y') }} Jeffrey Davidson. Built with
                <a
                    href="https://laravel.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hover:text-brand-600 text-gray-600 transition-colors dark:text-gray-400"
                >Laravel</a>
                &
                <a
                    href="https://filamentphp.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hover:text-brand-600 text-gray-600 transition-colors dark:text-gray-400"
                    >Filament</a
                >.
            </p>
            <p class="text-xs text-gray-600 dark:text-gray-400">Designed with ☕ in Florida</p>
        </div>
    </div>
</footer>
