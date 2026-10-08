<?php

use App\Models\Project;
use App\Models\Tag;
use App\Queries\ProjectListingQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Tags\Tag as SpatieTag;

pest()->use(RefreshDatabase::class);

it('lists published projects in sort order with their tags and every filter option', function () {
    $architecture = Tag::factory()->create(['name' => 'Architecture']);
    $laravel = Tag::factory()->create(['name' => 'Laravel']);
    $laterProject = Project::factory()->published()
        ->create(['sort_order' => 2, 'tech_stack' => ['laravel', 'php 10', 'Vue']]);
    $laterProject->attachTags([$laravel, $architecture]);
    $earlierProject = Project::factory()->published()
        ->create(['sort_order' => 1, 'tech_stack' => [' Laravel ', 'php 9', '']]);
    $earlierProject->attachTag($laravel);
    Project::factory()
        ->create(['tech_stack' => ['Rust']])
        ->attachTag(Tag::factory()->create(['name' => 'Draft topic']));

    $listing = app(ProjectListingQuery::class)
        ->get();

    expect($listing->projects->modelKeys())->toBe([$earlierProject->getKey(), $laterProject->getKey()])
        ->and($listing->projects->every(fn (Project $project): bool => $project->relationLoaded('tags')))
        ->toBeTrue()
        ->and($listing->technologies)
        ->toBe(['Laravel', 'php 9', 'php 10', 'Vue'])
        ->and(array_map(fn (SpatieTag $tag): string => $tag->name, $listing->tags))
        ->toBe(['Architecture', 'Laravel'])
        ->and($listing->technology)
        ->toBeNull()
        ->and($listing->tag)
        ->toBeNull();
});

it('narrows published projects to the requested filters', function (?string $technology, ?string $tagSlug, array $expectedTitles, ?string $matchedTechnology, ?string $matchedTagSlug) {
    $laravel = Tag::factory()->create(['name' => 'Laravel']);
    $vue = Tag::factory()->create(['name' => 'Vue']);
    Project::factory()->published()
        ->create(['title' => 'Laravel project', 'sort_order' => 1, 'tech_stack' => ['Laravel', 'Filament']])
        ->attachTag($laravel);
    Project::factory()->published()
        ->create(['title' => 'Vue project', 'sort_order' => 2, 'tech_stack' => ['Vue']])
        ->attachTag($vue);
    Project::factory()->published()
        ->create(['title' => 'Untagged project', 'sort_order' => 3, 'tech_stack' => null]);

    $listing = app(ProjectListingQuery::class)
        ->get($technology, $tagSlug);

    $titles = $listing->projects->pluck('title');

    expect($titles->all())->toBe($expectedTitles)
        ->and($listing->technology)
        ->toBe($matchedTechnology)
        ->and($listing->tag?->slug)
        ->toBe($matchedTagSlug);
})->with([
    'technology in any case' => ['filament', null, ['Laravel project'], 'Filament', null],
    'topic tag' => [null, 'vue', ['Vue project'], null, 'vue'],
    'both filters' => ['Laravel', 'laravel', ['Laravel project'], 'Laravel', 'laravel'],
    'filters with no shared project' => ['Laravel', 'vue', [], 'Laravel', 'vue'],
    'unknown technology' => ['Rust', null, [], null, null],
    'unknown topic' => [null, 'rust', [], null, null],
    'topic slug in another case' => [null, 'Vue', [], null, null],
]);
