<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders each registered resource index for an authorized user', function (string $resource) {
    $this->get($resource::getUrl('index'))->assertOk();
})->with([
    CategoryResource::class,
    EpisodeResource::class,
    PodcastResource::class,
    PostResource::class,
    ProjectResource::class,
    SubscriberResource::class,
    TagResource::class,
    TestimonialResource::class,
    UserResource::class,
    VideoResource::class,
]);

it('renders the video edit form for an authorized user', function () {
    $video = Video::query()->create([
        'youtube_id' => 'abc123',
        'title' => 'Typed Filament callbacks',
        'slug' => 'typed-filament-callbacks',
    ]);

    $this->get(VideoResource::getUrl('edit', ['record' => $video]))->assertOk();
});
