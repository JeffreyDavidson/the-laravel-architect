@props(['testimonials'])

@if($testimonials->isNotEmpty())
<section {{ $attributes->class(['border-t border-brand-800 bg-brand-950 py-12 text-white sm:py-16']) }}>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-home.section-header
            class="credibility-heading"
            eyebrow="Client perspective"
            title="Trusted when the codebase matters"
            description="Clear thinking, candid communication, and implementation that holds up after handoff."
        />

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3" role="list">
            @foreach($testimonials as $testimonial)
                <figure class="testimonial-card fade-up" role="listitem">
                    <blockquote class="flex-1">
                        <p class="text-pretty text-base text-gray-200">“{{ $testimonial->body }}”</p>
                    </blockquote>
                    <figcaption class="mt-5 border-t border-white/10 pt-5">
                        <p class="text-base font-medium text-white sm:text-sm">{{ $testimonial->name }}</p>
                        @if($testimonial->role || $testimonial->company)
                            <p class="mt-1 text-sm text-gray-400">{{ collect([$testimonial->role, $testimonial->company])->filter()->join(', ') }}</p>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('testimonials.create') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 underline decoration-brand-300 underline-offset-4 transition-colors hover:text-brand-600 dark:text-brand-300 dark:decoration-brand-700 dark:hover:text-brand-200">
                Worked with me? Share your experience
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</section>
@endif
