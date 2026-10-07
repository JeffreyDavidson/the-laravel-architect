<?php

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('creates a tag with the normalized slug generated from its name', function () {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'Tag name',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Tag::query()->sole()
        ->slug)->toBe('tag-name');
});

it('rejects a slug that is empty or cannot be normalized', function (string $name, string $slug) {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => $name,
            'slug' => $slug,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);

    expect(Tag::query()->exists())->toBeFalse();
})->with([
    'empty generated slug' => ['!!!', ''],
    'overlong generated slug' => [str_repeat('a', 256), str_repeat('a', 256)],
]);

it('rejects a duplicate localized tag slug across tag types', function () {
    Tag::factory()->create([
        'name' => 'Tag name',
        'type' => 'topic',
    ]);

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'Tag name',
            'slug' => 'tag-name',
            'type' => 'technology',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);

    expect(Tag::query()->count())->toBe(1);
});

it('rejects a duplicate localized tag slug when editing a tag', function () {
    Tag::factory()->create(['slug' => 'existing-tag']);
    $tag = Tag::factory()->create(['slug' => 'tag-name']);

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm([
            'name' => 'Existing tag',
            'slug' => 'existing-tag',
        ])
        ->call('save')
        ->assertHasFormErrors(['slug']);

    expect($tag->refresh()
        ->slug)->toBe('tag-name');
});

it('allows a tag to retain its localized slug when editing', function () {
    $tag = Tag::factory()->create([
        'name' => 'Tag name',
        'slug' => 'tag-name',
    ]);

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($tag->refresh())
        ->name->toBe('Tag name')
        ->slug->toBe('tag-name');
});

it('preserves an existing tag slug when the name changes', function () {
    $tag = Tag::factory()->create(['slug' => 'curated-tag-slug']);

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm(['name' => 'Updated tag name', 'slug' => 'curated-tag-slug'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($tag->refresh())
        ->name->toBe('Updated tag name')
        ->slug->toBe('curated-tag-slug');
});

it('resolves the validated localized slug on the public tag route', function () {
    $tag = Tag::factory()->create();
    $post = Post::factory()
        ->published()
        ->create(['title' => 'Tagged post']);
    $post->attachTag($tag);

    get(route('blog.tag', $tag))
        ->assertOk()
        ->assertSee('Tagged post');
});
