<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Widgets\WelcomeWidget;
use App\Models\ContactInquiry;
use App\Models\Post;
use App\Models\User;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('renders the publishing pipeline and attention queue', function () {
    $user = User::factory()->create();

    foreach ([PublishStatus::Published, PublishStatus::Published, PublishStatus::Draft, PublishStatus::InReview] as $index => $status) {
        Post::query()->create([
            'title' => "Post {$index}",
            'slug' => "post-{$index}",
            'content' => 'Content',
            'user_id' => $user->id,
            'status' => $status,
            'published_at' => $status === PublishStatus::Published ? now()->subDay() : null,
        ]);
    }

    ContactInquiry::factory()->create();

    livewire(WelcomeWidget::class)
        ->assertSee('Today in the studio')
        ->assertSee('4 total posts')
        ->assertSee('Publishing pipeline')
        ->assertSee('Needs attention')
        ->assertSee('Post awaiting review')
        ->assertSee('New contact inquiry')
        ->assertDontSee('Write post')
        ->assertSeeHtml('href="'.PostResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.ContactInquiryResource::getUrl('index').'"');
});

it('opens the matching post queue from each pipeline card', function (PublishStatus $status, string $selector) {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);

    foreach (PublishStatus::cases() as $postStatus) {
        Post::query()->create([
            'title' => "Queue result {$postStatus->value}",
            'slug' => "queue-result-{$postStatus->value}",
            'content' => 'Content',
            'user_id' => $user->id,
            'status' => $postStatus,
            'published_at' => match ($postStatus) {
                PublishStatus::Published => now()->subDay(),
                PublishStatus::Scheduled => now()->addDay(),
                default => null,
            },
        ]);
    }

    $widget = livewire(WelcomeWidget::class);
    $url = HTMLDocument::createFromString('<!DOCTYPE html><html><body>'.$widget->html().'</body></html>')->querySelector($selector)?->getAttribute('href');

    if ($url === null || $url === '') {
        throw new RuntimeException("The {$selector} queue link is missing.");
    }

    $response = get($url);

    $response->assertSuccessful()
        ->assertSee("Queue result {$status->value}");

    foreach (PublishStatus::cases() as $otherStatus) {
        if ($otherStatus !== $status) {
            $response->assertDontSee("Queue result {$otherStatus->value}");
        }
    }
})->with([
    'draft' => [PublishStatus::Draft, '.tla-dashboard-pipeline__step--draft'],
    'review' => [PublishStatus::InReview, '.tla-dashboard-pipeline__step--review'],
    'scheduled' => [PublishStatus::Scheduled, '.tla-dashboard-pipeline__step--scheduled'],
    'published' => [PublishStatus::Published, '.tla-dashboard-pipeline__step--published'],
    'attention queue' => [PublishStatus::InReview, '.tla-dashboard-attention__item--review'],
]);

it('keeps live and scheduled pipeline counts and destinations consistent across publication dates', function () {
    $this->freezeSecond();
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
    foreach ([
        ['Already live scheduled', PublishStatus::Scheduled, now()->subMinute()],
        ['Future published', PublishStatus::Published, now()->addDay()],
        ['No publication date', PublishStatus::Published, null],
    ] as [$title, $status, $date]) {
        Post::query()->create(['title' => $title, 'content' => 'Content', 'user_id' => $user->id, 'status' => $status, 'published_at' => $date]);
    }

    $widget = livewire(WelcomeWidget::class);

    $widget->assertViewHas('publishedPosts', 1)
        ->assertViewHas('scheduledPosts', 1);
    $document = HTMLDocument::createFromString('<!DOCTYPE html><html><body>'.$widget->html().'</body></html>');
    foreach (['published' => 'Already live scheduled', 'scheduled' => 'Future published'] as $queue => $title) {
        $url = $document->querySelector(".tla-dashboard-pipeline__step--{$queue}")?->getAttribute('href');

        if ($url === null || $url === '') {
            throw new RuntimeException("The {$queue} pipeline link is missing.");
        }

        $response = get($url);

        $response->assertSee($title)
            ->assertDontSee('No publication date')
            ->assertDontSee($queue === 'published' ? 'Future published' : 'Already live scheduled');
    }
});
