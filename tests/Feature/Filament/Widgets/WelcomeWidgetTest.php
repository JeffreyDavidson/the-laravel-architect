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
