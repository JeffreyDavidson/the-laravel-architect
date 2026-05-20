@props(['tag'])

<a href="{{ route('blog.tag', $tag) }}" class="relative z-10 rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs text-gray-500 transition-colors hover:border-brand-600/50 hover:text-brand-600 dark:border-brand-700 dark:bg-brand-900">
    {{ $tag->name }}
</a>
