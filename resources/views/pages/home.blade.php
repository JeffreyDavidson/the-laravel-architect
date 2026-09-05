@extends('layouts.app')

@push('head')
    @vite('resources/css/pages/home-entry.css')
@endpush

@section('content')
{{-- ===== HERO ===== --}}
<section class="homepage-hero">
    <picture class="hero-art" aria-hidden="true">
        <source
            media="(max-width: 767px)"
            srcset="{{ Vite::asset('resources/images/home-hero-mobile-640.webp') }} 640w, {{ Vite::asset('resources/images/home-hero-mobile-1024.webp') }} 1024w"
            sizes="100vw"
        >
        <source
            srcset="{{ Vite::asset('resources/images/home-hero-desktop-1024.webp') }} 1024w, {{ Vite::asset('resources/images/home-hero-desktop-1536.webp') }} 1536w"
            sizes="100vw"
        >
        <img
            src="{{ Vite::asset('resources/images/home-hero-desktop-1536.webp') }}"
            alt=""
            width="1536"
            height="1024"
            fetchpriority="high"
            decoding="async"
        >
    </picture>

    <div class="hero-shell mx-auto flex min-h-[calc(100svh-4rem)] max-w-7xl items-start px-4 py-16 sm:min-h-[calc(100svh-4.5rem)] sm:items-center sm:px-6 lg:px-8">
        <div class="hero-copy max-w-2xl">
            <p class="hero-intro">Jeffrey Davidson · The Laravel Architect</p>

            <h1 class="hero-title mt-5 text-5xl font-semibold leading-[1.02] tracking-[-0.045em] text-white sm:text-6xl xl:text-[4.5rem]">
                Laravel systems,<br>
                <span class="hero-title-accent">easier to change.</span>
            </h1>

            <p class="mt-6 max-w-lg text-lg leading-8 text-slate-300">
                Architecture, modernization, and hands-on development for teams carrying real production complexity.
            </p>

            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('contact') }}" class="hero-action hero-action-primary">Discuss a Project</a>
                <a href="{{ route('projects.index') }}" class="hero-action hero-action-secondary">View Projects</a>
            </div>
        </div>
    </div>
</section>

{{-- ===== TECH STACK MARQUEE ===== --}}
<section class="hidden" aria-hidden="true">
    <div class="marquee-track">
        @for($i = 0; $i < 4; $i++)
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934c0 .135-.073.26-.189.326l-9.037 5.206a.35.35 0 01-.128.049c-.01.004-.02.005-.03.01a.35.35 0 01-.2 0c-.013-.005-.025-.004-.038-.01a.376.376 0 01-.126-.049L.378 18.755a.378.378 0 01-.189-.326V3.334c0-.034.005-.07.014-.1.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033.012-.015.021-.032.036-.045.01-.01.025-.018.037-.027.014-.012.027-.024.041-.034h.001L4.896.384a.378.378 0 01.378 0L9.79 3.01h.002l.04.033.038.028c.014.013.023.03.035.045l.024.033c.01.019.015.038.024.058.005.012.011.02.013.033a.363.363 0 01.015.1v9.652l3.76-2.164V5.527c0-.034.005-.07.013-.1l.015-.033c.008-.02.014-.039.023-.058.01-.013.016-.022.024-.033.011-.015.02-.032.035-.045.012-.01.025-.018.038-.027l.04-.034h.002l4.518-2.624a.378.378 0 01.377 0l4.518 2.624c.015.01.027.021.042.033.012.01.025.018.036.028.016.013.025.03.037.045l.023.033c.01.019.017.038.024.058.005.012.011.02.014.033z"/></svg>
            Laravel
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312zm3.542 7.09c-.2.64-.54 1.122-.993 1.418-.46.302-1.048.453-1.74.453h-.876l-.417 2.146H9.869l1.469-7.563h2.535c.715 0 1.222.234 1.509.688.29.454.354 1.072.16 1.858z"/></svg>
            PHP
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0L1.608 6v12L12 24l10.392-6V6zm-1.2 17.4H8.4V13.2H6V10.8h2.4V6.6h2.4v4.2h2.4v2.4h-2.4zm6 0h-2.4V13.2H12V10.8h2.4V6.6h2.4v4.2H19.2v2.4h-2.4z"/></svg>
            Filament
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 4.8c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624C13.666 10.618 15.027 12 18.001 12c3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C16.337 6.182 14.976 4.8 12.001 4.8zm-6 7.2c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624 1.177 1.194 2.538 2.576 5.512 2.576 3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C10.337 13.382 8.976 12 6.001 12z"/></svg>
            Tailwind CSS
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3zm-4.4 15.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/></svg>
            Livewire
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.527.099C6.955-.744.942 3.9.099 10.473c-.843 6.572 3.8 12.584 10.373 13.428 6.573.843 12.587-3.801 13.428-10.374C24.744 6.955 20.101.943 13.527.099zm2.471 7.485a.855.855 0 00-.593.25l-4.453 4.453-.307-.307-.643-.643c4.389-4.376 5.18-4.418 5.996-3.753zm-4.863 4.861l4.44-4.44c.718-.32 1.755-.111 1.755.67 0 .324-.09.636-.344.89l-2.084 2.084-2.084 2.084a1.346 1.346 0 01-.89.344c-.781 0-.99-1.037-.67-1.755l-.123.123z"/></svg>
            Alpine.js
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.924 8.382l-5.808 9.614a.413.413 0 01-.708 0L5.6 8.382a.413.413 0 01.354-.627h3.742a.413.413 0 01.354.2l1.862 3.084a.413.413 0 00.708 0l1.862-3.084a.413.413 0 01.354-.2h3.742a.413.413 0 01.354.627z"/></svg>
            Vite
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm-.1 4.5h.2c3.3 0 5.6 2.1 5.6 5.2v.1c0 2-.9 3.4-2.4 4.3l3 4.3h-2.8l-2.6-3.8h-2.2v3.8H8.3V4.5h3.6zm.3 2.2h-1.4v4.8h1.4c2.1 0 3.2-.9 3.2-2.4v-.1c0-1.5-1.1-2.3-3.2-2.3z"/></svg>
            Redis
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M0 0h24v24H0V0zm22.034 18.276c-.175-1.095-.888-2.015-3.003-2.873-.736-.345-1.554-.585-1.797-1.14-.091-.33-.105-.51-.046-.705.15-.646.915-.84 1.515-.66.39.12.75.42.976.9 1.034-.676 1.034-.676 1.755-1.125-.27-.42-.405-.6-.586-.78-.63-.705-1.469-1.065-2.834-1.034l-.705.089c-.676.165-1.32.525-1.71 1.005-1.14 1.291-.811 3.541.569 4.471 1.365 1.02 3.361 1.244 3.616 2.205.24 1.17-.87 1.545-1.966 1.41-.811-.18-1.26-.586-1.755-1.336l-1.83 1.051c.21.48.45.689.81 1.109 1.74 1.756 6.09 1.666 6.871-1.004.029-.09.24-.705.074-1.65zm-8.983-7.245h-2.248c0 1.938-.009 3.864-.009 5.805 0 1.232.063 2.363-.138 2.711-.33.689-1.18.601-1.566.48-.396-.196-.597-.466-.83-.855-.063-.105-.11-.196-.127-.196l-1.825 1.125c.305.63.75 1.172 1.324 1.517.855.51 2.004.675 3.207.405.783-.226 1.458-.691 1.811-1.411.51-.93.402-2.07.397-3.346.012-2.054 0-4.109 0-6.179z"/></svg>
            JavaScript
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 4.8c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624C13.666 10.618 15.027 12 18.001 12c3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C16.337 6.182 14.976 4.8 12.001 4.8zm-6 7.2c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624 1.177 1.194 2.538 2.576 5.512 2.576 3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C10.337 13.382 8.976 12 6.001 12z"/></svg>
            CSS
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            GitHub
        </div>
        <div class="marquee-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
            Forge
        </div>
        @endfor
    </div>
</section>

<x-home.proof-strip
    years="15"
    :published-posts="$publishedPostCount"
    :published-projects="$publishedProjectCount"
    :recommendations="$approvedTestimonialCount"
/>

{{-- ===== ARCHITECTURE ===== --}}
<section class="architecture-section border-t border-gray-200 bg-white py-10 dark:border-brand-800/50 dark:bg-transparent sm:py-14">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.72fr_1.28fr] lg:items-center lg:px-8">
        <div class="max-w-md">
            <h2 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">A codebase should explain itself.</h2>
            <p class="mt-5 text-lg leading-8 text-gray-600 dark:text-gray-400">Clear boundaries make change safer, tests more useful, and production behavior easier to understand.</p>
        </div>

        <figure class="architecture-scene" data-architecture-scene data-architecture-state="idle" aria-labelledby="architecture-title architecture-description">
            <h3 id="architecture-title" class="architecture-scene-title">How a Laravel request moves</h3>

            <p id="architecture-description" class="sr-only">
                A Laravel request moves through routes, an application controller, the domain layer, and data services. Domain work can also dispatch queued jobs and events.
            </p>

            <div class="architecture-visual">
                <div class="architecture-canvas" data-architecture-canvas aria-hidden="true"></div>

                <div class="architecture-fallback" data-architecture-fallback>
                    <ol class="architecture-flow" aria-label="Primary request flow">
                        <li><span aria-hidden="true"></span><strong>Request</strong></li>
                        <li><span aria-hidden="true"></span><strong>Routes</strong></li>
                        <li><span aria-hidden="true"></span><strong>App</strong></li>
                        <li><span aria-hidden="true"></span><strong>Domain</strong></li>
                        <li><span aria-hidden="true"></span><strong>Data</strong></li>
                    </ol>

                    <p class="architecture-branch">Queue / events</p>
                </div>
            </div>

            <figcaption class="architecture-caption">
                Clear boundaries keep change local, behavior testable, and production work predictable.
            </figcaption>
        </figure>
    </div>
</section>

{{-- ===== FEATURED PROJECTS ===== --}}
@inject('projectImages', 'App\Services\ResponsiveImageVariants')
@if($featuredProjects->count())
<section class="case-studies-section border-t border-brand-800 bg-brand-950 py-14 text-white sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="case-studies-heading mb-10 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="case-studies-eyebrow mb-3 font-mono text-sm font-medium uppercase tracking-wide">
                    Selected work
                </p>
                <h2 class="text-balance text-3xl font-semibold tracking-tight sm:text-4xl">
                    Proof lives in the work
                </h2>
                <p class="mt-4 max-w-[62ch] text-pretty text-base sm:text-lg">
                    Products and platforms shaped around maintainability, clear domain boundaries, and dependable delivery.
                </p>
            </div>

            <a href="{{ route('projects.index') }}" class="inline-flex shrink-0 items-center gap-2 self-start rounded-lg px-3 py-2 text-base font-medium transition-colors hover:bg-brand-800/50 hover:text-brand-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-300 sm:self-auto sm:text-sm">
                View all projects
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
        <div class="case-study-grid">
            @foreach($featuredProjects as $index => $project)
            <a href="{{ route('projects.show', $project) }}" class="case-study-card {{ $index === 0 ? 'case-study-lead' : '' }} group fade-up">
                @if($project->featured_image_url)
                @php
                    $featuredImageSrcset = $projectImages->srcset($project->featured_image_path);
                @endphp
                <div class="case-study-art">
                    <picture class="block h-full">
                        @if($featuredImageSrcset)
                        <source
                            type="image/webp"
                            srcset="{{ $featuredImageSrcset }}"
                            sizes="{{ $index === 0 ? '(min-width: 1024px) 760px, calc(100vw - 2rem)' : '(min-width: 1024px) 390px, calc(100vw - 2rem)' }}"
                        >
                        @endif
                        <img src="{{ $project->featured_image_url }}" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </picture>
                </div>
                @elseif($index === 0)
                <div class="case-study-art">
                    <picture class="block h-full">
                        <source
                            type="image/webp"
                            srcset="{{ Vite::asset('resources/images/home-case-study-fallback-768.webp') }} 768w, {{ Vite::asset('resources/images/home-case-study-fallback-1280.webp') }} 1280w"
                            sizes="(min-width: 1024px) 760px, calc(100vw - 2rem)"
                        >
                        <img src="{{ Vite::asset('resources/images/home-case-study-fallback-1280.webp') }}" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </picture>
                </div>
                @endif
                <div class="case-study-copy p-6">
                    <h3 class="project-card-title mb-2 text-xl font-semibold text-white transition-colors group-hover:text-brand-200">{{ $project->title }}</h3>
                    <p class="mb-4 text-sm leading-6 text-gray-300">{{ $project->description }}</p>
                    @if($project->tech_stack)
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tech_stack as $tech)
                        <span class="rounded-full border border-brand-700 px-2.5 py-1 text-xs font-medium text-brand-200">{{ $tech }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<x-home.credibility :testimonials="$testimonials" />

{{-- ===== WHAT I DO ===== --}}
<section class="engagement-section border-t border-gray-200 bg-white py-14 dark:border-brand-800/50 dark:bg-transparent sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-home.section-header
            class="fade-up"
            eyebrow="Ways to work together"
            title="Bring me the consequential work"
            description="The work usually falls into three clear engagements, each grounded in shipping—not slide decks."
        />
        <div class="engagement-grid">
            <a href="{{ route('contact') }}" class="engagement-card group fade-up">
                <span class="engagement-number">01</span>
                <h3>Build</h3>
                <p>Design and deliver a Laravel product with clean boundaries from the first production release.</p>
                <span class="engagement-link">Start a build <span aria-hidden="true">→</span></span>
            </a>
            <a href="{{ route('contact') }}" class="engagement-card group fade-up">
                <span class="engagement-number">02</span>
                <h3>Modernize</h3>
                <p>Move a legacy system forward without losing the behavior and knowledge the business depends on.</p>
                <span class="engagement-link">Plan the migration <span aria-hidden="true">→</span></span>
            </a>
            <a href="{{ route('contact') }}" class="engagement-card group fade-up">
                <span class="engagement-number">03</span>
                <h3>Review</h3>
                <p>Get a candid architecture and code review, prioritized around risk, leverage, and what to do next.</p>
                <span class="engagement-link">Book a review <span aria-hidden="true">→</span></span>
            </a>
        </div>
    </div>
</section>

{{-- ===== LATEST POSTS ===== --}}
<section class="writing-section border-t border-gray-200 bg-gray-50 py-14 dark:border-brand-800/50 dark:bg-transparent sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($latestPosts->count())
        <x-home.section-header
            eyebrow="Latest writing"
            title="Practical notes from the work"
            description="Architecture decisions, Laravel techniques, and lessons from maintaining real applications."
            :href="route('blog.index')"
            link-label="Browse all articles"
        />
        <div class="blog-posts-grid">
            {{-- Featured post --}}
            @if($latestPosts->first())
            @php $featured = $latestPosts->first(); @endphp
            <article class="blog-featured group fade-up overflow-hidden rounded-xl border border-gray-200 bg-white transition-colors duration-200 hover:border-brand-600/40 dark:border-brand-800/50 dark:bg-brand-900/60">
                <a href="{{ route('blog.show', $featured) }}" class="blog-featured-link">
                    <x-post-artwork :post="$featured" sizes="(min-width: 1024px) 720px, calc(100vw - 2rem)" class="blog-featured-art" />
                    <div class="blog-featured-copy p-7 sm:p-9">
                    @if($featured->category)
                    <span class="text-xs font-semibold text-brand-400 uppercase tracking-wide">{{ $featured->category->name }}</span>
                    @endif
                    <h3 class="font-semibold text-2xl md:text-3xl text-gray-900 dark:text-white mt-2 mb-4 group-hover:text-brand-400 transition-colors">{{ $featured->title }}</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-base line-clamp-3 max-w-3xl">{{ $featured->excerpt }}</p>
                    <div class="mt-5 flex items-center gap-3 text-xs text-gray-500">
                        <time datetime="{{ $featured->published_at->toDateString() }}">{{ $featured->published_at->format('M d, Y') }}</time>
                        <span>·</span>
                        <span>{{ $featured->reading_time }} min read</span>
                    </div>
                    </div>
                </a>
            </article>
            @endif

            {{-- Remaining posts --}}
            @if($latestPosts->count() > 1)
            <div class="blog-posts-rest">
                @foreach($latestPosts->skip(1) as $post)
                <article class="group fade-up overflow-hidden rounded-xl border border-gray-200 bg-white transition-colors duration-200 hover:border-brand-600/40 dark:border-brand-800/50 dark:bg-brand-900/60">
                    <a href="{{ route('blog.show', $post) }}" class="blog-compact-link">
                        <x-post-artwork :post="$post" sizes="(min-width: 640px) 280px, calc(100vw - 2rem)" class="blog-compact-art" />
                        <div class="p-6">
                        @if($post->category)
                        <span class="text-xs font-semibold text-brand-400 uppercase tracking-wide">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-white mt-2 mb-3 group-hover:text-brand-400 transition-colors">{{ $post->title }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="mt-4 flex items-center gap-3 text-xs text-gray-500">
                            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M d, Y') }}</time>
                            <span>·</span>
                            <span>{{ $post->reading_time }} min read</span>
                        </div>
                        </div>
                    </a>
                </article>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        <div class="mt-12 fade-up sm:mt-16">
            <x-home.newsletter-signup class="writing-newsletter" />
        </div>
    </div>
</section>

{{-- ===== MEDIA ===== --}}
<section class="media-section border-t border-gray-200 bg-white py-14 dark:border-brand-800/50 dark:bg-transparent sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-home.section-header
            eyebrow="Field notes"
            title="Watch, listen, and build along"
            description="Long-form conversations and practical build sessions for developers who care about what happens after launch."
        />

        <div class="media-grid">
            <x-home.podcast-card
                title="Coffee With The Laravel Architect"
                description="Conversations about Laravel, web development, and the developer life. One cup at a time."
                :image="Vite::asset('resources/images/podcast-coffee-logo-128.webp')"
                image-alt="Coffee With The Laravel Architect"
                :episode-count="$coffeeEpisodeCount ?? null"
                :href="route('podcast.index')"
            />

            @if($latestYouTubeVideos->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach($latestYouTubeVideos->take(2) as $video)
                        <x-home.youtube-thumbnail-card :video="$video" />
                    @endforeach
                </div>
            @else
                <a href="{{ config('public-site.youtube.url') }}" target="_blank" rel="noopener noreferrer" class="group rounded-xl border border-gray-200 bg-white p-7 transition-colors hover:border-brand-500/50 dark:border-brand-800/60 dark:bg-brand-900/40">
                    <p class="font-mono text-xs uppercase tracking-[0.16em] text-gray-500">The Laravel Architect</p>
                    <p class="mt-3 text-xl font-semibold text-gray-900 transition-colors group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-300">Follow the channel for the next build session.</p>
                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">New videos focus on maintainable Laravel architecture, testing, and production-minded development.</p>
                </a>
            @endif
        </div>

        <div class="mt-7 flex flex-wrap gap-3">
            <a href="{{ route('podcast.index') }}" class="media-link">Browse the podcast <span aria-hidden="true">→</span></a>
            <a href="{{ config('public-site.youtube.url') }}" target="_blank" rel="noopener noreferrer" class="media-link">Visit the YouTube channel <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>

{{-- ===== FINAL CTA ===== --}}
<section class="border-t border-gray-200 bg-gray-50 dark:border-white/5 dark:bg-[#0D1117]">
    <div class="mx-auto max-w-4xl px-4 py-20 text-center sm:px-6 md:py-28 lg:px-8">
        <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-green-300 bg-green-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-green-800 dark:border-green-500/20 dark:bg-green-500/5 dark:text-green-400">
            <span class="h-2 w-2 rounded-full bg-green-500 dark:bg-green-400" aria-hidden="true"></span>
            Available for Projects
        </div>

        <h2 class="mb-6 text-4xl font-extrabold leading-tight text-gray-900 dark:text-white sm:text-5xl">
            Let's build something maintainable.
        </h2>

        <p class="mx-auto mb-10 max-w-xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">
            Freelance Laravel development, legacy modernization, consulting, and collaborations. Let's talk.
        </p>

        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('contact') }}" class="group inline-flex items-center gap-2 rounded-xl bg-brand-600 px-8 py-4 text-lg font-semibold text-white transition-colors hover:bg-brand-500">
                Get in Touch
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-8 py-4 text-lg font-semibold text-gray-700 transition-colors hover:border-brand-500 hover:text-brand-700 dark:border-brand-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-white">
                View My Work
            </a>
        </div>
    </div>
</section>

@endsection
