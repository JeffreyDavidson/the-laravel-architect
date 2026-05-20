<x-filament-widgets::widget>
    <section class="tla-dashboard-hero">
        <div class="tla-dashboard-hero__content">
            <div class="tla-dashboard-hero__kicker">
                <span class="tla-dashboard-hero__dot"></span>
                The Laravel Architect
            </div>

            <div class="tla-dashboard-hero__main">
                <div>
                    <h2>Publishing overview</h2>
                    <p>
                        Track the content pipeline, proof-of-work, newsletter audience, and media library from one local workspace.
                    </p>
                </div>

                <div class="tla-dashboard-hero__actions">
                    <a href="{{ route('filament.admin.resources.posts.create') }}">Write post</a>
                    <a href="/" target="_blank">
                        <span></span>
                        Public site
                    </a>
                </div>
            </div>

            <div class="tla-dashboard-stats">
                <a href="{{ route('filament.admin.resources.posts.index') }}" class="tla-dashboard-stat tla-dashboard-stat--blue">
                    <span>Posts</span>
                    <strong>{{ $posts }}</strong>
                    <small>{{ $publishedPosts }} published / {{ $draftPosts }} drafts</small>
                </a>

                <a href="{{ route('filament.admin.resources.projects.index') }}" class="tla-dashboard-stat tla-dashboard-stat--pink">
                    <span>Projects</span>
                    <strong>{{ $projects }}</strong>
                    <small>{{ $featuredProjects }} featured</small>
                </a>

                <a href="{{ route('filament.admin.resources.subscribers.index') }}" class="tla-dashboard-stat tla-dashboard-stat--green">
                    <span>Subscribers</span>
                    <strong>{{ $subscribers }}</strong>
                    <small>Newsletter list</small>
                </a>

                <a href="{{ route('filament.admin.resources.videos.index') }}" class="tla-dashboard-stat tla-dashboard-stat--amber">
                    <span>Videos</span>
                    <strong>{{ $videos }}</strong>
                    <small>Publishing assets</small>
                </a>
            </div>

            @if($pendingTestimonials > 0)
                <a href="{{ route('filament.admin.resources.testimonials.index') }}" class="tla-dashboard-alert">
                    <span>{{ $pendingTestimonials }}</span>
                    {{ $pendingTestimonials === 1 ? 'testimonial needs' : 'testimonials need' }} review
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endif
        </div>
    </section>
</x-filament-widgets::widget>
