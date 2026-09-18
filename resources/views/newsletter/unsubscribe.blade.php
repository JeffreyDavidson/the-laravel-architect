<x-layouts.site :seo-source="$seoSource ?? null">
    <x-slot:head>
        @include('partials.json-ld')
    </x-slot:head>
    <x-page-section>
        <div class="mx-auto max-w-xl text-center">
            <x-terminal-prompt command="newsletter:unsubscribe" />
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                Unsubscribe from the newsletter
            </h1>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                Stop newsletter updates to {{ $subscriber->email }}. You can subscribe again at any time.
            </p>

            <form action="{{ $actionUrl }}" method="POST" class="mt-8">
                @csrf
                @method('DELETE')
                <x-button type="submit">Unsubscribe</x-button>
            </form>
        </div>
    </x-page-section>
    </x-layouts.app>
