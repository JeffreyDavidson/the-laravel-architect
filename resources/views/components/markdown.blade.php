@props([
    'content',
    'headingIds' => false,
])

@php
    $options = [
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ];

    $extensions = [];

    if ($headingIds) {
        $options['heading_permalink'] = [
            'insert' => 'none',
            'apply_id_to_heading' => true,
            'id_prefix' => '',
        ];
        $extensions[] = new League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
    }
@endphp

<div {{ $attributes->class('prose prose-lg max-w-none dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-brand-600 prose-strong:text-gray-900 dark:prose-strong:text-white') }}>
    {!! Str::markdown($content, $options, $extensions) !!}
</div>
