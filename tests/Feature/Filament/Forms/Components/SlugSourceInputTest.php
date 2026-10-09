<?php

use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Podcasts\Pages\CreatePodcast;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

dataset('slug source create pages', [
    'post' => [CreatePost::class, 'title'],
    'project' => [CreateProject::class, 'title'],
    'episode' => [CreateEpisode::class, 'title'],
    'newsletter issue' => [CreateNewsletterIssue::class, 'title'],
    'podcast' => [CreatePodcast::class, 'name'],
    'tag' => [CreateTag::class, 'name'],
]);

it('fills the slug from the source field when creating content', function (string $page, string $source) {
    livewire($page)
        ->fillForm([$source => 'Shipping Laravel Faster'])
        ->assertSchemaStateSet(['slug' => 'shipping-laravel-faster']);
})->with('slug source create pages');

it('keeps a slug the editor already entered when creating content', function (string $page, string $source) {
    livewire($page)
        ->fillForm(['slug' => 'my-chosen-slug'])
        ->fillForm([$source => 'Shipping Laravel Faster'])
        ->assertSchemaStateSet(['slug' => 'my-chosen-slug']);
})->with('slug source create pages');

it('leaves the slug alone when the title changes on an existing draft', function () {
    $post = PublishableFixtures::readyPost();

    livewire(PublishableFixtures::editPage('post'), ['record' => $post->getRouteKey()])
        ->fillForm(['title' => 'A completely different title'])
        ->assertSchemaStateSet(['slug' => 'ready-post']);
});
