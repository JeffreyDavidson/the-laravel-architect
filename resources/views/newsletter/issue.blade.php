<x-layouts.site :seo-source="$seoSource ?? null" :structured-data="$structuredData ?? []">
    <x-slot:head>
        @vite('resources/css/pages/article-entry.css')
    </x-slot:head>

    <article>
        <header class="mx-auto max-w-4xl px-4 pt-12 pb-8 sm:px-6 sm:pt-16 sm:pb-10 lg:px-8 lg:pt-20">
            <a
                href="{{ route('newsletter.index') }}"
                class="text-brand-600 dark:text-brand-300 font-mono text-sm font-semibold tracking-wide uppercase hover:underline"
            >
                Newsletter archive
            </a>
            <h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-pretty text-gray-950 sm:text-5xl lg:text-6xl dark:text-white">
                {{ $issue->title }}
            </h1>
            @if ($issue->excerpt)
                <p class="mt-6 max-w-2xl text-lg text-pretty text-gray-600 sm:text-xl dark:text-gray-300">
                    {{ $issue->excerpt }}
                </p>
            @endif
            <time
                class="mt-7 block text-base text-gray-600 sm:text-sm dark:text-gray-400"
                datetime="{{ $issue->published_at?->toDateString() }}"
            >
                Published {{ $issue->published_at?->format('F j, Y') }}
            </time>
        </header>

        <div class="mx-auto max-w-3xl px-4 pb-12 sm:px-6 sm:pb-16 lg:px-8 lg:pb-20">
            <x-prose class="article-prose prose-a:text-brand-600 dark:prose-a:text-brand-300 prose-code:text-brand-300 max-w-none">
                {!!
                    Str::markdown(
                        $issue->content,
                        [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                            'heading_permalink' => ['insert' => 'none', 'apply_id_to_heading' => true, 'id_prefix' => ''],
                        ],
                    )
                !!}
            </x-prose>

            <div class="mt-12 border-t border-gray-200 pt-8 dark:border-gray-800">
                <a
                    href="{{ route('newsletter.index') }}"
                    class="text-brand-600 dark:text-brand-300 text-sm font-semibold hover:underline"
                >← Back to newsletter archive</a>
            </div>
        </div>
    </article>
</x-layouts.site>
