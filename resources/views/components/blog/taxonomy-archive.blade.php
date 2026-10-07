@props([
    'eyebrow',
    'title',
    'posts',
    'emptyMessage',
    'description' => null,
    'showCategory' => true,
])

{{-- The header and paginated post list shared by the blog category and tag archives. --}}
<x-page-header :back-href="route('blog.index')" back-label="All Posts">
    <x-slot:eyebrow class="mb-4">{{ $eyebrow }}</x-slot:eyebrow>
    <x-slot:title class="mb-4 text-4xl font-bold tracking-tight text-gray-900 md:text-6xl dark:text-white">
        {{ $title }}
    </x-slot:title>
    <x-slot:description class="max-w-2xl text-lg text-gray-600 dark:text-gray-400">
        {{ $description }}
    </x-slot:description>
</x-page-header>

<div class="dark:bg-surface-page bg-gray-50">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-16 lg:px-8">
        <div class="space-y-6">
            @forelse ($posts as $post)
                <x-blog-card :post="$post" :showCategory="$showCategory" :showTags="false" />
            @empty
                <x-empty-state :message="$emptyMessage" />
            @endforelse

            @if ($posts->hasPages())
                <div class="pt-8">{{ $posts->links() }}</div>
            @endif
        </div>
    </div>
</div>
