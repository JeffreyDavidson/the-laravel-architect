<?php

use Illuminate\Support\Facades\Blade;

/**
 * Render the search highlight component for a piece of result text.
 */
function renderSearchHighlight(string $text, string $query): string
{
    return Blade::render('<x-search-highlight :text="$text" :query="$query" />', [
        'text' => $text,
        'query' => $query,
    ]);
}

it('highlights every match of the query in its original case', function () {
    expect(renderSearchHighlight('Laravel and laravel', 'laravel'))
        ->toBe('<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark> and <mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">laravel</mark>')
        ->and(renderSearchHighlight('Testing Laravel queues.', 'laravel'))
        ->toBe('Testing <mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark> queues.');
});

it('escapes HTML inside and outside a match', function (string $text, string $query, string $expected) {
    expect(renderSearchHighlight($text, $query))->toBe($expected);
})->with([
    'markup around a match' => [
        '<b>Laravel</b> tips',
        'Laravel',
        '&lt;b&gt;<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark>&lt;/b&gt; tips',
    ],
    'markup and quotes beside a match' => [
        'alert(1) Laravel & "friends"',
        'Laravel',
        'alert(1) <mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark> &amp; &quot;friends&quot;',
    ],
    'markup inside a match' => [
        '<script>alert(1)</script>',
        '<script>',
        '<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">&lt;script&gt;</mark>alert(1)&lt;/script&gt;',
    ],
    'text without a match' => [
        'No match here.',
        '<script>',
        'No match here.',
    ],
    'an ampersand query in a title' => [
        'Q&A',
        '&',
        'Q<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">&amp;</mark>A',
    ],
    'an ampersand query in a description' => [
        'Questions & answers',
        '&',
        'Questions <mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">&amp;</mark> answers',
    ],
]);

it('never highlights inside an escaped entity', function (string $query) {
    expect(renderSearchHighlight('Q&A <tips> "Laravel"', $query))
        ->toBe('Q&amp;A &lt;tips&gt; &quot;Laravel&quot;')
        ->and(renderSearchHighlight('Q&A  "Laravel"', $query))
        ->toBe('Q&amp;A  &quot;Laravel&quot;');
})->with(['amp', 'lt', 'gt', 'quot']);

it('escapes the text without highlighting when there is no query', function () {
    expect(renderSearchHighlight('<b>Laravel</b>', ''))->toBe('&lt;b&gt;Laravel&lt;/b&gt;');
});
