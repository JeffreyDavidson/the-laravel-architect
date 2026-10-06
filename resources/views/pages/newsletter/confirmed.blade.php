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
    </x-page-section>
</x-layouts.site>
