<x-filament-widgets::widget>
    <section class="tla-dashboard-hero">
        <div class="tla-dashboard-hero__content">
            <div class="tla-dashboard-hero__main">
                <div>
                    <h2>Today in the studio</h2>
                    <p>Move the next piece of content forward, respond to readers, and keep the public site ready.</p>
                </div>

                <div class="tla-dashboard-hero__actions">
                    <a href="/" target="_blank" rel="noreferrer">
                        <span></span>
                        Public site
                    </a>
                </div>
            </div>

            <div class="tla-dashboard-pipeline" aria-label="Publishing pipeline">
                <div class="tla-dashboard-pipeline__header">
                    <div>
                        <strong>Publishing pipeline</strong>
                        <span>{{ $posts }} total {{ $posts === 1 ? 'post' : 'posts' }}</span>
                    </div>

                    <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('index') }}">Open posts</a>
                </div>

                <div class="tla-dashboard-pipeline__steps">
                    <a
                        href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('index', ['filters' => ['status' => ['value' => \App\Enums\PublishStatus::Draft->value]]]) }}"
                        class="tla-dashboard-pipeline__step tla-dashboard-pipeline__step--draft"
                    >
                        <span>Draft</span>
                        <strong>{{ $draftPosts }}</strong>
                        <small>In progress</small>
                    </a>

                    <a
                        href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('index', ['filters' => ['status' => ['value' => \App\Enums\PublishStatus::InReview->value]]]) }}"
                        class="tla-dashboard-pipeline__step tla-dashboard-pipeline__step--review"
                    >
                        <span>Review</span>
                        <strong>{{ $inReviewPosts }}</strong>
                        <small>Awaiting review</small>
                    </a>

                    <a
                        href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('index', ['filters' => ['publication' => ['value' => \App\Enums\PublishStatus::Scheduled->value]]]) }}"
                        class="tla-dashboard-pipeline__step tla-dashboard-pipeline__step--scheduled"
                    >
                        <span>Scheduled</span>
                        <strong>{{ $scheduledPosts }}</strong>
                        <small>Queued to publish</small>
                    </a>

                    <a
                        href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('index', ['filters' => ['publication' => ['value' => \App\Enums\PublishStatus::Published->value]]]) }}"
                        class="tla-dashboard-pipeline__step tla-dashboard-pipeline__step--published"
                    >
                        <span>Published</span>
                        <strong>{{ $publishedPosts }}</strong>
                        <small>Live on the site</small>
                    </a>
                </div>
            </div>

            @if ($inReviewPosts > 0 || $newInquiries > 0)
                <div class="tla-dashboard-attention" aria-label="Needs attention">
                    <div class="tla-dashboard-attention__header">
                        <strong>Needs attention</strong>
                        <span>Clear these before the next publishing pass.</span>
                    </div>

                    <div class="tla-dashboard-attention__items">
                        @if ($inReviewPosts > 0)
                            <a
                                href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('index', ['filters' => ['status' => ['value' => \App\Enums\PublishStatus::InReview->value]]]) }}"
                                class="tla-dashboard-attention__item tla-dashboard-attention__item--review"
                            >
                                <strong>{{ $inReviewPosts }}</strong>
                                <span>
                                    <b>{{ $inReviewPosts === 1 ? 'Post' : 'Posts' }} awaiting review</b>
                                    <small>Open the editorial queue</small>
                                </span>
                            </a>
                        @endif

                        @if ($newInquiries > 0)
                            <a
                                href="{{ \App\Filament\Resources\ContactInquiries\ContactInquiryResource::getUrl('index') }}"
                                class="tla-dashboard-attention__item tla-dashboard-attention__item--inquiries"
                            >
                                <strong>{{ $newInquiries }}</strong>
                                <span>
                                    <b>{{ $newInquiries === 1 ? 'New contact inquiry' : 'New contact inquiries' }}</b>
                                    <small>Respond from the inquiry inbox</small>
                                </span>
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="tla-dashboard-caught-up" role="status">
                    <span aria-hidden="true"></span>
                    Nothing is waiting for review or a reply.
                </div>
            @endif
        </div>
    </section>
</x-filament-widgets::widget>
