@props([
    'content',
    'headingIds' => false,
])

<div {{ $attributes->class('prose prose-lg max-w-none dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-brand-action prose-strong:text-gray-900 dark:prose-strong:text-white') }}>
    {!! app(JeffreyDavidson\CreatorKit\Support\Markdown\MarkdownRenderer::class)->safe($content, headingIds: $headingIds) !!}
</div>
