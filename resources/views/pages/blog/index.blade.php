<x-layouts.site :seo-source="$seoSource ?? null" :structured-data="$structuredData ?? []">
    <x-slot:head>
        @vite('resources/css/pages/listings-entry.css')
    </x-slot:head>

    <div class="blog-index dark:bg-surface-page bg-gray-50">
        <header class="dark:border-surface-border dark:bg-surface-page border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-16 lg:px-8">
                <h1
                    id="blog-heading"
                    class="max-w-4xl text-4xl font-semibold tracking-[-0.035em] text-balance text-gray-900 sm:text-5xl md:text-6xl dark:text-gray-100"
                >
                    Notes from the work.
                </h1>
                <p class="mt-5 max-w-2xl text-lg text-pretty text-gray-600 dark:text-gray-400">
                    Practical writing about Laravel architecture, testing, and the decisions behind maintainable
                    applications.
                </p>
            </div>
        </header>

        <livewire:blog-index :search="$query ?? ''" :category-slug="$categorySlug ?? null" />
    </div>
</x-layouts.site>
