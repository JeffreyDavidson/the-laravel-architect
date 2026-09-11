@extends('layouts.app')

@section('title', 'Services')

@section('content')
    @php
        $services = [
            ['id' => 'build', 'icon' => 'heroicon-o-squares-2x2', 'title' => 'Build your application', 'description' => 'Turn the way your business works into software that supports it. From a new product to an internal tool, start with the workflows that matter most.', 'examples' => ['Custom Laravel applications and new features', 'Admin panels and tools for everyday operations', 'Data modeling and integrations'], 'tools' => 'Laravel · Filament · Livewire'],
            ['id' => 'improve', 'icon' => 'heroicon-o-wrench-screwdriver', 'title' => 'Improve an existing codebase', 'description' => 'You don’t always need a rewrite. Find what’s slowing your team down, preserve what works, and make focused changes that are easier to maintain.', 'examples' => ['Code reviews and practical technical priorities', 'Laravel and PHP upgrades', 'Refactoring backed by regression tests'], 'tools' => 'PHP · Laravel · Code review'],
            ['id' => 'ship', 'icon' => 'heroicon-o-shield-check', 'title' => 'Ship with confidence', 'description' => 'Make important behavior easier to verify and releases easier to repeat. Build the safety net around the workflows your business depends on.', 'examples' => ['Automated tests for critical user journeys', 'Continuous integration and deployment workflows', 'Production monitoring and error visibility'], 'tools' => 'Pest · Laravel Forge · Production monitoring'],
        ];
    @endphp

    <header class="dark:border-brand-800 border-b border-gray-200 bg-white dark:bg-[#0b1016]">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-[1.3fr_1fr] lg:items-end lg:gap-20 lg:px-8">
            <div>
                <h1 class="max-w-2xl text-4xl leading-tight font-semibold tracking-tight text-balance text-gray-900 sm:text-5xl lg:text-6xl dark:text-white">
                    Good software.<br /><span class="text-brand-700 dark:text-brand-300">A clearer path forward.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-gray-600 dark:text-gray-300">
                    I help teams build Laravel applications, improve existing code, and release changes with confidence.
                </p>
                <x-button href="{{ route('contact') }}" class="mt-8 min-h-11 gap-3 px-6 py-3">
                    Discuss your project
                    <x-heroicon-o-arrow-long-right class="size-5" aria-hidden="true" />
                </x-button>
            </div>
            <nav
                aria-label="Explore services"
                class="dark:divide-brand-800 dark:border-brand-800 divide-y divide-gray-200 border-y border-gray-200"
            >
                @foreach ($services as $service)
                    <a
                        href="#{{ $service['id'] }}"
                        class="group hover:text-brand-700 focus-visible:outline-brand-500 dark:hover:text-brand-300 flex min-h-20 items-center gap-5 py-5 text-base font-medium text-gray-900 transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 dark:text-white"
                    >
                        <x-dynamic-component
                            :component="$service['icon']"
                            class="text-brand-600 dark:text-brand-300 size-8 shrink-0"
                            aria-hidden="true"
                        />
                        <span class="flex-1">{{ $service['title'] }}</span>
                        <x-heroicon-o-arrow-down
                            class="size-5 shrink-0 motion-safe:transition-transform motion-safe:group-hover:translate-y-1"
                            aria-hidden="true"
                        />
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    <div class="dark:divide-brand-800 mx-auto max-w-7xl divide-y divide-gray-200 px-4 sm:px-6 lg:px-8">
        @foreach ($services as $service)
            <section
                id="{{ $service['id'] }}"
                aria-labelledby="{{ $service['id'] }}-heading"
                class="grid scroll-mt-24 gap-8 py-12 sm:py-16 lg:grid-cols-2 lg:gap-20"
            >
                <div>
                    <x-dynamic-component
                        :component="$service['icon']"
                        class="text-brand-600 dark:text-brand-300 mb-6 size-12"
                        aria-hidden="true"
                    />
                    <h2
                        id="{{ $service['id'] }}-heading"
                        class="max-w-md text-3xl font-semibold tracking-tight text-balance text-gray-900 sm:text-4xl dark:text-white"
                    >
                        {{ $service['title'] }}
                    </h2>
                    <p class="mt-5 max-w-lg text-lg leading-8 text-gray-600 dark:text-gray-300">
                        {{ $service['description'] }}
                    </p>
                </div>
                <div class="lg:pt-18">
                    <ul class="space-y-5">
                        @foreach ($service['examples'] as $example)
                            <li class="flex items-start gap-3 text-base leading-7 text-gray-700 dark:text-gray-200">
                                <x-heroicon-o-check
                                    class="text-brand-600 dark:text-brand-300 mt-1 size-5 shrink-0"
                                    aria-hidden="true"
                                />
                                {{ $example }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="dark:border-brand-800 mt-8 border-t border-gray-200 pt-5 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ $service['tools'] }}
                    </p>
                </div>
            </section>
        @endforeach
    </div>

    <section
        aria-labelledby="starting-heading"
        class="dark:border-brand-800 border-y border-gray-200 bg-gray-50 dark:bg-[#0b1016]"
    >
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-2 lg:gap-20 lg:px-8">
            <div>
                <h2
                    id="starting-heading"
                    class="text-3xl font-semibold tracking-tight text-balance text-gray-900 sm:text-4xl dark:text-white"
                >
                    Start with the problem.<br />We’ll work out the next step.
                </h2>
                <p class="mt-5 max-w-lg text-lg leading-8 text-gray-600 dark:text-gray-300">
                    You don’t need a finished specification to get in touch. Tell me what you’re building, what’s
                    getting in the way, and any timeline you have in mind.
                </p>
                <a
                    href="{{ route('projects.index') }}"
                    class="text-brand-700 hover:text-brand-900 focus-visible:outline-brand-500 dark:text-brand-300 mt-6 inline-flex min-h-11 items-center gap-3 rounded font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 dark:hover:text-white"
                    >Explore my projects <x-heroicon-o-arrow-long-right class="size-5" aria-hidden="true"
                /></a>
            </div>
            <ol class="space-y-7">
                <li>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Share the context</h3>
                    <p class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-300">
                        Your goals, the current application, and the people who use it.
                    </p>
                </li>
                <li>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Agree on a focused scope</h3>
                    <p class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-300">
                        Identify a useful first milestone and how we’ll know it’s working.
                    </p>
                </li>
                <li>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Build, review, and refine</h3>
                    <p class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-300">
                        Keep the work understandable, test the important behavior, and review progress together.
                    </p>
                </li>
            </ol>
        </div>
    </section>

    <section
        aria-labelledby="contact-heading"
        class="mx-auto flex max-w-7xl flex-col items-start gap-7 px-4 py-14 sm:px-6 sm:py-20 lg:flex-row lg:items-center lg:justify-between lg:px-8"
    >
        <div>
            <h2
                id="contact-heading"
                class="text-3xl font-semibold tracking-tight text-balance text-gray-900 dark:text-white"
            >
                What would you like to move forward?
            </h2>
            <p class="mt-3 text-lg text-gray-600 dark:text-gray-300">Let’s talk about where I can help.</p>
        </div>
        <x-button href="{{ route('contact') }}" class="min-h-11 shrink-0 gap-3 px-6 py-3"
            >Discuss your project <x-heroicon-o-arrow-long-right class="size-5" aria-hidden="true"
        /></x-button>
    </section>
@endsection
