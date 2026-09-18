@props([
    'seoSource' => null,
    'structuredData' => [],
])

@php
    $content = $slot->toHtml();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script nonce="{{ Vite::cspNonce() }}">
        // Sync theme before paint to prevent a flash of the wrong color scheme.
        if (
            localStorage.theme === 'light' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)
        ) {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link rel="icon" type="image/png" sizes="32x32" href="/images/elephant-companion-32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="/images/elephant-companion-16.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/images/elephant-companion-180.png" />
    <link rel="manifest" href="/site.webmanifest" />
    <meta name="theme-color" content="transparent" />
    <link rel="alternate" type="application/rss+xml" title="The Laravel Architect" href="/rss" />
    {!! seo($seoSource ?? null) !!}
    <x-json-ld :schemas="$structuredData" />
    @if (request()->routeIs('blog.index'))
        @livewireStyles
    @endif
    @if (config('services.fathom.site_id'))
        <script
            nonce="{{ Vite::cspNonce() }}"
            src="https://cdn.usefathom.com/script.js"
            data-site="{{ config('services.fathom.site_id') }}"
            defer
        ></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {!! $head ?? '' !!}
</head>
<body
    @if (session('fathom_event')) data-fathom-event-on-load="{{ session('fathom_event') }}" @endif
    class="dark:bg-brand-950 bg-white font-sans text-gray-800 antialiased dark:text-gray-100"
>
    <a
        href="#main-content"
        class="bg-brand-600 focus:outline-brand-300 z-overlay sr-only rounded-lg px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:outline-2 focus:outline-offset-2"
    >
        Skip to content
    </a>

    <x-site.header />

    @if (request()->routeIs('preview.*'))
        <div
            class="border-b border-amber-500/20 bg-amber-500/10 px-4 py-3 text-center text-sm font-semibold text-amber-700 dark:text-amber-300"
            role="status"
        >
            Preview mode · This content is not public yet.
        </div>
    @endif

    <main
        id="main-content"
        tabindex="-1"
        @class([
            'isolate',
            '[&_h1]:font-semibold [&_h1]:text-balance [&_h1]:tracking-[-0.025em] [&_h1]:leading-normal [&_h2]:font-semibold [&_h2]:text-balance [&_h2]:tracking-[-0.025em] [&_h3]:font-semibold [&_h3]:text-balance [&_p]:text-pretty' => request()->routeIs('home'),
        ])
    >
        {!! $content ?? '' !!}
    </main>

    <x-site.footer />
    @stack('scripts')
    @if (request()->routeIs('blog.index'))
        @livewireScriptConfig
    @endif
</body>
</html>
