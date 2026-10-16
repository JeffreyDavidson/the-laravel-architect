<?php

use App\Filament\Widgets\WelcomeWidget;
use App\Models\ContactInquiry;
use App\Models\Post;
use App\Models\User;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\ContactInquiries\ContactInquiryResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('renders the publishing pipeline and attention queue', function () {
    Post::factory()
        ->count(2)
        ->published()
        ->create();
    Post::factory()->create();
    Post::factory()
        ->inReview()
        ->create();

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
        Post::factory()->create([
            'title' => "Queue result {$postStatus->value}",
            'status' => $postStatus,
            'published_at' => match ($postStatus) {
                PublishStatus::Published => now()->subDay(),
                PublishStatus::Scheduled => now()->addDay(),
                default => null,
            },
        ]);
    }

    $widget = livewire(WelcomeWidget::class);
    $url = HTMLDocument::createFromString('<!DOCTYPE html><html><body>'.$widget->html().'</body></html>')->querySelector($selector)
        ?->getAttribute('href');

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

it('counts live and scheduled pipeline posts by publication date', function () {
    // Arrange
    freezeSecond();
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);

    Post::factory()->create([
        'title' => 'Already live scheduled',
        'status' => PublishStatus::Scheduled,
        'published_at' => now()->subMinute(),
    ]);
    Post::factory()->create([
        'title' => 'Future published',
        'status' => PublishStatus::Published,
        'published_at' => now()->addDay(),
    ]);
    Post::factory()->create([
        'title' => 'No publication date',
        'status' => PublishStatus::Published,
        'published_at' => null,
    ]);

    // Act
    $widget = livewire(WelcomeWidget::class);

    // Assert
    $widget->assertViewHas('publishedPosts', 1)
        ->assertViewHas('scheduledPosts', 1);
});

it('opens a pipeline queue that lists posts by publication date', function (string $queue, string $listedTitle, string $excludedTitle) {
    // Arrange
    freezeSecond();
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);

    Post::factory()->create([
        'title' => 'Already live scheduled',
        'status' => PublishStatus::Scheduled,
        'published_at' => now()->subMinute(),
    ]);
    Post::factory()->create([
        'title' => 'Future published',
        'status' => PublishStatus::Published,
        'published_at' => now()->addDay(),
    ]);
    Post::factory()->create([
        'title' => 'No publication date',
        'status' => PublishStatus::Published,
        'published_at' => null,
    ]);

    $widget = livewire(WelcomeWidget::class);
    $document = HTMLDocument::createFromString('<!DOCTYPE html><html><body>'.$widget->html().'</body></html>');
    $url = $document->querySelector(".tla-dashboard-pipeline__step--{$queue}")
        ?->getAttribute('href');

    if ($url === null || $url === '') {
        throw new RuntimeException("The {$queue} pipeline link is missing.");
    }

    // Act
    $response = get($url);

    // Assert
    $response->assertSee($listedTitle)
        ->assertDontSee('No publication date')
        ->assertDontSee($excludedTitle);
})->with([
    'published' => ['published', 'Already live scheduled', 'Future published'],
    'scheduled' => ['scheduled', 'Future published', 'Already live scheduled'],
]);
