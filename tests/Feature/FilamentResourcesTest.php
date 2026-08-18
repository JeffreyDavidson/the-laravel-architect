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
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('super_admin');
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
