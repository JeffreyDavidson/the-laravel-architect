<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

it('creates a tag with the normalized slug generated from its name', function () {
    Livewire::test(CreateTag::class)
        ->fillForm([
            'name' => 'Tag name',
            'slug' => 'tag-name',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Tag::query()->sole()->slug)->toBe('tag-name');
});

it('rejects a name that cannot generate a valid slug', function (string $name) {
    Livewire::test(CreateTag::class)
        ->fillForm([
            'name' => $name,
            'slug' => 'spoofed-valid-slug',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);

    expect(Tag::query()->exists())->toBeFalse();
})->with([
    'empty generated slug' => '!!!',
    'overlong generated slug' => str_repeat('a', 256),
]);

it('rejects a duplicate localized tag slug across tag types', function () {
    Tag::query()->create([
        'name' => 'Tag name',
        'type' => 'topic',
    ]);

    Livewire::test(CreateTag::class)
        ->fillForm([
            'name' => 'Tag name',
            'slug' => 'spoofed-unique-slug',
            'type' => 'technology',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);

    expect(Tag::query()->count())->toBe(1);
});

it('rejects a duplicate localized tag slug when editing a tag', function () {
    Tag::query()->create([
        'name' => 'Existing tag',
        'slug' => 'existing-tag',
    ]);
    $tag = Tag::query()->create([
        'name' => 'Tag name',
        'slug' => 'tag-name',
    ]);

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm([
            'name' => 'Existing tag',
            'slug' => 'spoofed-unique-slug',
        ])
        ->call('save')
        ->assertHasFormErrors(['slug']);

    expect($tag->refresh()->slug)->toBe('tag-name');
});

it('allows a tag to retain its localized slug when editing', function () {
    $tag = Tag::query()->create([
        'name' => 'Tag name',
        'slug' => 'tag-name',
    ]);

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($tag->refresh())
        ->name->toBe('Tag name')
        ->slug->toBe('tag-name');
});

it('resolves the validated localized slug on the public tag route', function () {
    $tag = Tag::query()->create([
        'name' => 'Tag name',
        'slug' => 'tag-name',
    ]);
    $post = Post::query()->create([
        'title' => 'Tagged post',
        'slug' => 'tagged-post',
        'content' => 'Tagged post content',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subMinute(),
    ]);
    $post->attachTag($tag);

    $this->get(route('blog.tag', $tag))
        ->assertOk()
        ->assertSee('Tagged post');
});
