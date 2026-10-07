<?php

use App\Filament\Resources\Videos\VideoResource;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the video edit page for an authorized user', function () {
    $video = Video::factory()->create();

    get(VideoResource::getUrl('edit', ['record' => $video]))
        ->assertOk();
});
