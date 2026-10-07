<?php

use Illuminate\Support\Facades\Blade;

it('gives a plain title and description the standard page header styles', function () {
    $html = Blade::render('<x-page-header eyebrow="Archive" eyebrow-variant="section" title="Everything" description="Browse it all." />');

    expect($html)
        ->toContain('<p class="text-brand-600 dark:text-brand-300 font-mono text-sm font-semibold tracking-wide uppercase">Archive</p>')
        ->toContain('<h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl dark:text-white">')
        ->toContain('<p class="mt-5 max-w-2xl text-lg text-pretty text-gray-600 dark:text-gray-400">')
        ->not
        ->toContain('All Posts');
});

it('lets a title slot with its own classes replace the standard styles', function () {
    $html = Blade::render(<<<'BLADE'
        <x-page-header>
            <x-slot:title id="blog-heading" class="text-4xl">Notes</x-slot:title>
        </x-page-header>
        BLADE);

    expect($html)
        ->toContain('<h1 id="blog-heading" class="text-4xl">')
        ->not
        ->toContain('max-w-3xl');
});

it('omits an empty description', function () {
    $html = Blade::render('<x-page-header title="Laravel" :description="null" />');

    expect($html)->not->toContain('text-pretty');
});

it('pads full-width headers from md and narrower headers from sm', function (string $attributes, string $classes) {
    expect(Blade::render("<x-page-header {$attributes} title=\"Title\" />"))
        ->toContain("<div class=\"mx-auto px-4 sm:px-6 lg:px-8 {$classes}\">");
})->with([
    'full width' => ['', 'max-w-7xl py-14 md:py-20'],
    'full width, compact' => ['compact', 'max-w-7xl py-12 md:py-16'],
    'narrow' => ['width="6xl"', 'max-w-6xl py-14 sm:py-20'],
    'narrow, compact' => ['width="4xl" compact', 'max-w-4xl py-12 sm:py-16'],
]);

it('links back when given a back link', function () {
    $html = Blade::render('<x-page-header back-href="/blog" back-label="All Posts" title="Laravel" />');

    expect($html)
        ->toContain('href="/blog"')
        ->toContain('All Posts');
});
