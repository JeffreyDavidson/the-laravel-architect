<x-layouts.site :page-meta="$pageMeta">
    <div class="blog-index dark:bg-surface-page bg-gray-50">
        <x-page-header compact>
            <x-slot:title
                id="blog-heading"
                class="max-w-4xl text-4xl font-semibold tracking-[-0.035em] text-balance text-gray-900 sm:text-5xl md:text-6xl dark:text-gray-100"
            >
                Notes from the work.
            </x-slot:title>
            <x-slot:description>
                Practical writing about Laravel architecture, testing, and the decisions behind maintainable
                applications.
            </x-slot:description>
        </x-page-header>

        <livewire:blog-index
            :search="$query ?? ''"
            :category-slug="$categorySlug ?? null"
            :initial-data="$initialData"
        />
    </div>
</x-layouts.site>
