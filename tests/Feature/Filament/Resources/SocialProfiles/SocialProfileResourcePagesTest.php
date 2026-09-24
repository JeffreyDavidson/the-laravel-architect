<?php

use App\Filament\Resources\SocialProfiles\SocialProfileResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('allows administrators to manage social profiles', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(SocialProfileResource::getUrl('index'))
        ->assertOk()
        ->assertSee('GitHub');
});

it('forbids non-administrators from managing social profiles', function () {
    $this->actingAs(User::factory()->create())
        ->get(SocialProfileResource::getUrl('index'))
        ->assertForbidden();
});
