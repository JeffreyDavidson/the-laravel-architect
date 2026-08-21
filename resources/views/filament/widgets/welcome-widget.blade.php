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
                    <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('create') }}">Write post</a>
                    <a href="/" target="_blank" rel="noreferrer">
                        <span></span>
                        Public site
                    </a>
                </div>
            </div>

            <div class="tla-dashboard-stats">
                <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('index') }}" class="tla-dashboard-stat tla-dashboard-stat--blue">
                    <span>Posts</span>
                    <strong>{{ $posts }}</strong>
                    <small>{{ $publishedPosts }} published / {{ $draftPosts }} drafts</small>
                </a>

                <a href="{{ \App\Filament\Resources\Projects\ProjectResource::getUrl('index') }}" class="tla-dashboard-stat tla-dashboard-stat--pink">
                    <span>Projects</span>
                    <strong>{{ $projects }}</strong>
                    <small>{{ $featuredProjects }} featured</small>
                </a>

                <a href="{{ \App\Filament\Resources\Subscribers\SubscriberResource::getUrl('index') }}" class="tla-dashboard-stat tla-dashboard-stat--green">
                    <span>Subscribers</span>
                    <strong>{{ $subscribers }}</strong>
                    <small>Newsletter list</small>
                </a>

                <a href="{{ \App\Filament\Resources\Videos\VideoResource::getUrl('index') }}" class="tla-dashboard-stat tla-dashboard-stat--amber">
                    <span>Videos</span>
                    <strong>{{ $videos }}</strong>
                    <small>Publishing assets</small>
                </a>
            </div>

            @if($pendingTestimonials > 0 || $inReviewPosts > 0)
                <div class="tla-dashboard-attention" aria-label="Needs attention">
                    <span class="tla-dashboard-attention__label">Needs attention</span>

                    @if($inReviewPosts > 0)
                        <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('index') }}">
                            <strong>{{ $inReviewPosts }}</strong>
                            {{ $inReviewPosts === 1 ? 'post is' : 'posts are' }} awaiting review
                        </a>
                    @endif

                    @if($pendingTestimonials > 0)
                        <a href="{{ \App\Filament\Resources\Testimonials\TestimonialResource::getUrl('index') }}">
                            <strong>{{ $pendingTestimonials }}</strong>
                            {{ $pendingTestimonials === 1 ? 'testimonial needs' : 'testimonials need' }} review
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
</x-filament-widgets::widget>
