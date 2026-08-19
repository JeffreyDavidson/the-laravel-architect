<?php

use App\Enums\PublishStatus;
use App\Filament\Widgets\WelcomeWidget;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('renders the publishing and project statistics', function () {
    $user = User::factory()->create();

    foreach ([PublishStatus::Published, PublishStatus::Published, PublishStatus::Draft, PublishStatus::InReview] as $index => $status) {
        Post::query()->create([
            'title' => "Post {$index}",
            'slug' => "post-{$index}",
            'content' => 'Content',
            'user_id' => $user->id,
            'status' => $status,
        ]);
    }

    Project::query()->create([
        'title' => 'Featured project',
        'slug' => 'featured-project',
        'description' => 'Description',
        'is_featured' => true,
    ]);
    Project::query()->create([
        'title' => 'Standard project',
        'slug' => 'standard-project',
        'description' => 'Description',
    ]);

    livewire(WelcomeWidget::class)
        ->assertSee('4')
        ->assertSee('2 published / 1 drafts')
        ->assertSee('2')
        ->assertSee('1 featured');
});
