<x-layouts.site :seo-source="$seoSource ?? null">
    <x-page-section>
        <div class="mx-auto max-w-xl text-center">
            <x-terminal-prompt command="newsletter:confirmed" />
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">You’re confirmed</h1>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                Thanks for confirming. New issues will land in your inbox when I publish them, and every one has an
                unsubscribe link at the bottom if it stops being useful.
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-button :href="route('blog.index')">Read the blog</x-button>
                <x-button :href="route('home')" variant="outline">Back to home</x-button>
            </div>
        </div>

        @if ($latestPosts->isNotEmpty())
            <section aria-labelledby="latest-posts-heading" class="mt-16">
                <h2
                    id="latest-posts-heading"
                    class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white"
                >
                    While you’re here
                </h2>
                <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-12 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($latestPosts as $post)
                        <div class="flex min-w-0">
                            <x-blog-card :post="$post" editorial :showTags="false" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </x-page-section>
</x-layouts.site>
