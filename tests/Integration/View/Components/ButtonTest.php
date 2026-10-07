<?php

use Illuminate\Support\Facades\Blade;

it('appends caller classes after the variant and size classes', function () {
    $html = Blade::render('<x-button href="/contact" size="sm" class="w-fit">Contact</x-button>');

    expect($html)
        ->toContain('<a href="/contact" class="inline-flex items-center gap-2 font-bold')
        ->toContain('rounded-xl px-4 py-2 text-sm w-fit">Contact</a>');
});

it('renders a submit button without a trailing class separator', function () {
    $html = Blade::render('<x-button type="submit">Send</x-button>');

    expect($html)
        ->toContain('<button type="submit" class="inline-flex')
        ->toContain('px-6 py-3 text-sm">Send</button>');
});

it('leaves size to the caller on the flat brand variants', function (string $variant, string $classes) {
    $html = Blade::render('<x-button :variant="$variant" size="lg" class="px-5">Go</x-button>', ['variant' => $variant]);

    expect($html)
        ->toContain("class=\"{$classes} px-5\"")
        ->not
        ->toContain('px-8 py-4 text-lg');
})->with([
    'brand' => ['brand', 'focus-visible:outline-brand-500 bg-brand-600 hover:bg-brand-700 rounded-lg px-5 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-4'],
    'brand-soft' => ['brand-soft', 'bg-brand-600 hover:bg-brand-500 rounded-lg font-semibold text-white'],
]);
