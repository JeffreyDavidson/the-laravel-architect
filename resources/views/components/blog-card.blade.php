@props(['post', 'showCategory' => true, 'showTags' => true, 'showExcerpt' => true])
<div class="blog-card group rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117] overflow-hidden">
    <div class="p-6 md:p-8">
        <x-post-meta :post="$post" :showCategory="$showCategory" class="mb-3" />

        <a href="{{ route('blog.show', $post) }}">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-[#4A7FBF] transition-colors">{{ $post->title }}</h2>
        </a>

        @if($showExcerpt && $post->excerpt)
        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed line-clamp-2 mb-4">{{ $post->excerpt }}</p>
        @endif

        @if($showTags && $post->tags && $post->tags->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">
            @foreach($post->tags as $tag)
            <a href="{{ route('blog.tag', $tag) }}" class="px-2.5 py-1 text-xs rounded-full border border-gray-200 dark:border-[#1e2a3a] text-gray-500 bg-gray-50 dark:bg-[#161b22] hover:border-[#4A7FBF]/50 hover:text-[#4A7FBF] transition-colors relative z-10">{{ $tag->name }}</a>
            @endforeach
        </div>
        @endif
    </div>
</div>
