<?php

use App\Filament\Resources\Videos\VideoResource;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the video edit page for an authorized user', function () {
    $video = Video::query()->create([
        'youtube_id' => 'abc123',
        'title' => 'Video page coverage',
        'slug' => 'video-page-coverage',
    ]);

    $this->get(VideoResource::getUrl('edit', ['record' => $video]))
        ->assertOk();
});
