@props(['tag'])

<a href="{{ route('blog.tag', $tag) }}" class="px-2.5 py-1 text-xs rounded-full border border-gray-200 dark:border-[#1e2a3a] text-gray-500 bg-gray-50 dark:bg-[#161b22] hover:border-[#4A7FBF]/50 hover:text-[#4A7FBF] transition-colors relative z-10">
    {{ $tag->name }}
</a>
