@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'prose dark:prose-invert prose-lg max-w-none prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-[#4A7FBF] prose-strong:text-gray-900 dark:prose-strong:text-white ' . $class]) }}>
    {{ $slot }}
</div>
