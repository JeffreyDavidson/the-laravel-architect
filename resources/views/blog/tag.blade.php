<x-layouts.site
    :seo-source="$seoSource ?? null"
    :post="$post ?? null"
    :podcast="$podcast ?? null"
    :episode="$episode ?? null"
    :project="$project ?? null"
    :posts="$posts ?? null"
    :selected-category="$selectedCategory ?? null"
    :category="$category ?? null"
    :tag="$tag ?? null"
    :projects="$projects ?? null"
    :episodes="$episodes ?? null"
    :items="$items ?? null"
>
    <x-slot:head>
        @vite('resources/css/pages/listings-entry.css')
    </x-slot:head>

    {{-- Hero --}}
    <header class="dark:border-surface-border dark:bg-surface-page border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
            <a
                href="{{ route('blog.index') }}"
                class="text-brand-600 hover:text-brand-500 mb-4 inline-flex items-center gap-1 text-sm transition-colors"
            >
                <x-svg-icon name="chevron-left" class="h-4 w-4" />
                All Posts
            </a>
            <p class="text-brand-600 tracking-label mb-4 font-mono text-xs uppercase">Tagged note</p>
            <h1 class="mb-4 text-4xl font-bold tracking-tight text-gray-900 md:text-6xl dark:text-white">
                {{ $tag->name }}
            </h1>
        </div>
    </header>

    {{-- Posts --}}
    <div class="dark:bg-surface-page bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-16 lg:px-8">
            <div class="space-y-6">
                @forelse ($posts as $post)
                    <x-blog-card :post="$post" :showTags="false" />
                @empty
                    <x-empty-state message="No posts with this tag yet." />
                @endforelse

                @if ($posts->hasPages())
                    <div class="pt-8">{{ $posts->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.site>
