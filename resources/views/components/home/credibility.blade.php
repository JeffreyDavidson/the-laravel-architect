@props(['testimonials'])

<section {{ $attributes->class(['noise-overlay bg-gray-50 py-12 dark:bg-transparent sm:py-20']) }}>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-home.section-header
            eyebrow="Working principles"
            title="Architecture is more than the code"
            description="Clean boundaries, useful tests, and honest communication are the standards I bring to every engagement."
        />

        <blockquote class="fade-up rounded-2xl border border-brand-200 bg-white p-6 shadow-sm dark:border-brand-800/50 dark:bg-brand-900/50 dark:shadow-none sm:p-8">
            <p class="text-pretty text-xl font-medium text-gray-900 dark:text-white sm:text-2xl">
                “Clean architecture, tested code, and honest conversations. That’s what I bring to every project.”
            </p>
            <footer class="mt-4 text-base font-medium text-brand-700 dark:text-brand-300 sm:text-sm">
                Jeffrey Davidson
            </footer>
        </blockquote>

        @if($testimonials->isNotEmpty())
            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" role="list">
                @foreach($testimonials as $testimonial)
                    <figure class="testimonial-card fade-up" role="listitem">
                        <blockquote class="flex-1">
                            <p class="text-pretty text-base text-gray-600 dark:text-gray-300 sm:text-sm">“{{ $testimonial->body }}”</p>
                        </blockquote>
                        <figcaption class="mt-5 border-t border-gray-200 pt-5 dark:border-white/5">
                            <p class="text-base font-medium text-gray-900 dark:text-white sm:text-sm">{{ $testimonial->name }}</p>
                            @if($testimonial->role || $testimonial->company)
                                <p class="mt-1 text-sm text-gray-500">{{ collect([$testimonial->role, $testimonial->company])->filter()->join(', ') }}</p>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</section>
