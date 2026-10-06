<?php

use App\ViewModels\SearchViewModel;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Run one search result through the ViewModel and return its highlighted title and description.
 *
 * @return array{title: string, description: string|null}
 */
function highlightSearchResult(string $title, ?string $description, string $query): array
{
    $results = [
        'posts' => new LengthAwarePaginator([[
            'title' => $title,
            'description' => $description,
            'url' => 'https://thelaravelarchitect.test/blog/result',
            'meta' => 'Post',
            'external' => false,
        ]], 1, 10),
    ];

    $data = app(SearchViewModel::class)
        ->data($results, $query);
    $item = $data['results']['posts']->items()[0];

    return [
        'title' => $item['highlightedTitle'],
        'description' => $item['highlightedDescription'],
    ];
}

it('highlights every match of the query in its original case', function () {
    $highlighted = highlightSearchResult('Laravel and laravel', 'Testing Laravel queues.', 'laravel');

    expect($highlighted)->toBe([
        'title' => '<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark> and <mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">laravel</mark>',
        'description' => 'Testing <mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark> queues.',
    ]);
});

it('escapes HTML in titles and descriptions, inside and outside a match', function (string $title, string $description, string $query, string $expectedTitle, string $expectedDescription) {
    $highlighted = highlightSearchResult($title, $description, $query);

    expect($highlighted)->toBe([
        'title' => $expectedTitle,
        'description' => $expectedDescription,
    ]);
})->with([
    'markup around a match' => [
        '<b>Laravel</b> tips',
        '<script>alert(1)</script> Laravel & "friends"',
        'Laravel',
        '&lt;b&gt;<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark>&lt;/b&gt; tips',
        'alert(1) <mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">Laravel</mark> &amp; &quot;friends&quot;',
    ],
    'markup inside a match' => [
        '<script>alert(1)</script>',
        'No match here.',
        '<script>',
        '<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">&lt;script&gt;</mark>alert(1)&lt;/script&gt;',
        'No match here.',
    ],
    'an ampersand query' => [
        'Q&A',
        'Questions & answers',
        '&',
        'Q<mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">&amp;</mark>A',
        'Questions <mark class="rounded bg-brand-100 px-0.5 text-inherit dark:bg-brand-800">&amp;</mark> answers',
    ],
]);

it('never highlights inside an escaped entity', function (string $query) {
    $highlighted = highlightSearchResult('Q&A <tips> "Laravel"', 'Q&A <tips> "Laravel"', $query);

    expect($highlighted)->toBe([
        'title' => 'Q&amp;A &lt;tips&gt; &quot;Laravel&quot;',
        'description' => 'Q&amp;A  &quot;Laravel&quot;',
    ]);
})->with(['amp', 'lt', 'gt', 'quot']);

it('escapes the result without highlighting when there is no query', function () {
    $highlighted = highlightSearchResult('<b>Laravel</b>', null, '');

    expect($highlighted)->toBe([
        'title' => '&lt;b&gt;Laravel&lt;/b&gt;',
        'description' => null,
    ]);
});
