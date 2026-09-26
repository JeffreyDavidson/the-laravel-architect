@php
    $primaryLinks = [
        ['label' => 'Services', 'route' => 'services', 'active' => 'services'],
        ['label' => 'Projects', 'route' => 'projects.index', 'active' => 'projects.*'],
        ['label' => 'Writing', 'route' => 'blog.index', 'active' => 'blog.*'],
        ['label' => 'About', 'route' => 'about', 'active' => 'about'],
    ];
    $utilityLinks = [
        ['label' => 'Search', 'route' => 'search', 'active' => 'search'],
        ['label' => 'Archive', 'route' => 'archive.index', 'active' => 'archive.*'],
    ];
@endphp

<header class="sticky top-0 z-50">
    <nav
        x-data="siteHeader"
        x-on:keydown.escape="closeMenu"
        class="dark:border-brand-800/50 dark:bg-brand-950/90 border-b border-gray-200 bg-white/90 backdrop-blur-lg"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a
                    href="{{ route('home') }}"
                    class="group focus-visible:outline-brand-500 flex shrink-0 items-center gap-3 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4"
                >
                    <img
                        src="/images/elephant-companion-128.webp"
                        alt=""
                        width="52"
                        height="52"
                        decoding="async"
                        class="size-13 shrink-0 object-contain"
                    />
                    <span class="flex flex-col gap-0.5 leading-none">
                        <span class="text-brand-600 group-hover:text-brand-500 dark:text-brand-300 dark:group-hover:text-brand-200 text-meta tracking-micro font-mono font-medium uppercase transition-colors">The Laravel</span>
                        <span class="font-empera group-hover:text-brand-600 dark:group-hover:text-brand-200 text-2xl leading-none tracking-[0.04em] text-gray-950 transition-colors dark:text-white">Architect</span>
                    </span>
                </a>

                <button
                    id="mobile-menu-btn"
                    x-ref="menuButton"
                    x-on:click="toggleMenu"
                    x-bind:aria-expanded="menuOpen"
                    class="focus-visible:outline-brand-500 dark:hover:bg-brand-800 inline-flex size-12 items-center justify-center rounded-md p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-offset-2 lg:hidden dark:text-gray-400 dark:hover:text-white"
                    aria-label="Toggle menu"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="hamburger-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="hidden items-center gap-6 lg:flex">
                    <x-site.nav-links :links="$primaryLinks" />
                    <div class="dark:border-brand-800 flex items-center gap-4 border-l border-gray-200 pl-5">
                        <x-site.nav-links :links="$utilityLinks" secondary />
                    </div>
                    <x-site.theme-toggle />
                    <x-button
                        href="{{ route('contact') }}"
                        size="sm"
                        class="w-fit"
                        :aria-current="request()->routeIs('contact') ? 'page' : null"
                    >Discuss a Project</x-button>
                </div>
            </div>

            <div
                id="mobile-menu"
                hidden
                x-bind:hidden="menuClosed"
                class="dark:border-brand-800/50 border-t border-gray-200 py-4 lg:hidden"
            >
                <div class="flex flex-col gap-3">
                    <x-site.nav-links :links="$primaryLinks" mobile />
                    <div class="dark:border-brand-800 flex flex-col gap-3 border-t border-gray-200 pt-3">
                        <x-site.nav-links :links="$utilityLinks" mobile secondary />
                    </div>
                    <x-site.theme-toggle mobile />
                    <x-button
                        href="{{ route('contact') }}"
                        size="sm"
                        class="w-fit"
                        :aria-current="request()->routeIs('contact') ? 'page' : null"
                    >Discuss a Project</x-button>
                </div>
            </div>
        </div>
    </nav>
</header>
