<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\SocialProfile;
use App\Models\Tag;
use App\Models\Video;
use App\Publishing\ContentReadiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

pest()->use(RefreshDatabase::class);

dataset('factory states', [
    'category' => fn (): Category => Category::factory()->create(),
    'draft episode' => fn (): Episode => Episode::factory()->create(),
    'published episode' => fn (): Episode => Episode::factory()
        ->published()
        ->create(),
    'scheduled episode' => fn (): Episode => Episode::factory()
        ->scheduled()
        ->create(),
    'newsletter delivery' => fn (): NewsletterDelivery => NewsletterDelivery::factory()->create(),
    'draft newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::factory()->create(),
    'published newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::factory()
        ->published()
        ->create(),
    'scheduled newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::factory()
        ->scheduled()
        ->create(),
    'sent newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::factory()
        ->sent()
        ->create(),
    'active podcast' => fn (): Podcast => Podcast::factory()->create(),
    'inactive podcast' => fn (): Podcast => Podcast::factory()
        ->inactive()
        ->create(),
    'draft post' => fn (): Post => Post::factory()->create(),
    'post in review' => fn (): Post => Post::factory()
        ->inReview()
        ->create(),
    'published post' => fn (): Post => Post::factory()
        ->published()
        ->create(),
    'scheduled post' => fn (): Post => Post::factory()
        ->scheduled()
        ->create(),
    'draft project' => fn (): Project => Project::factory()->create(),
    'published project' => fn (): Project => Project::factory()
        ->published()
        ->create(),
    'featured project' => fn (): Project => Project::factory()
        ->featured()
        ->create(),
    'social profile' => fn (): SocialProfile => SocialProfile::factory()->create(),
    'tag' => fn (): Tag => Tag::factory()->create(),
    'video' => fn (): Video => Video::factory()->create(),
]);

dataset('default publishable records', [
    'post' => fn (): Post => Post::factory()->create(),
    'project' => fn (): Project => Project::factory()->create(),
    'episode' => fn (): Episode => Episode::factory()->create(),
    'newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::factory()->create(),
]);

dataset('published records', [
    'post' => fn (): Post => Post::factory()
        ->published()
        ->create(),
    'project' => fn (): Project => Project::factory()
        ->published()
        ->create(),
    'episode' => fn (): Episode => Episode::factory()
        ->published()
        ->create(),
    'newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::factory()
        ->published()
        ->create(),
]);

dataset('scheduled records', [
    'post' => fn (): Post => Post::factory()
        ->scheduled()
        ->create(),
    'episode' => fn (): Episode => Episode::factory()
        ->scheduled()
        ->create(),
    'newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::factory()
        ->scheduled()
        ->create(),
]);

it('saves a record that satisfies every constraint', function (Model $record) {
    $saved = $record::query()
        ->whereKey($record->getKey())
        ->exists();

    expect($saved)->toBeTrue()
        ->and(DB::select('pragma foreign_key_check'))
        ->toBeEmpty();
})->with('factory states');

it('creates publishable content as a ready draft outside the published scope', function (Post|Project|Episode|NewsletterIssue $record) {
    $published = $record::query()
        ->published()
        ->whereKey($record->getKey())
        ->exists();

    expect($record->status)->toBe(PublishStatus::Draft)
        ->and($published)
        ->toBeFalse()
        ->and(new ContentReadiness($record)->publishingIssues())
        ->toBeEmpty();
})->with('default publishable records');

it('puts the published state in the published scope', function (Post|Project|Episode|NewsletterIssue $record) {
    $published = $record::query()
        ->published()
        ->whereKey($record->getKey())
        ->exists();

    expect($record->status)->toBe(PublishStatus::Published)
        ->and($published)
        ->toBeTrue();
})->with('published records');

it('keeps the scheduled state out of the published scope until its date', function (Post|Episode|NewsletterIssue $record) {
    $published = $record::query()
        ->published()
        ->whereKey($record->getKey())
        ->exists();
    $scheduled = $record::query()
        ->scheduled()
        ->whereKey($record->getKey())
        ->exists();

    expect($record->status)->toBe(PublishStatus::Scheduled)
        ->and($published)
        ->toBeFalse()
        ->and($scheduled)
        ->toBeTrue();
})->with('scheduled records');

it('marks a sent newsletter issue as published and emailed', function () {
    $issue = NewsletterIssue::factory()
        ->sent()
        ->create();

    expect($issue->isPublished())->toBeTrue()
        ->and($issue->wasSent())
        ->toBeTrue();
});

it('keeps an inactive podcast out of the active scope', function () {
    $active = Podcast::factory()->create();
    $inactive = Podcast::factory()
        ->inactive()
        ->create();

    $activeIds = Podcast::query()
        ->active()
        ->pluck('id')
        ->all();

    expect($activeIds)->toBe([$active->id])
        ->and($activeIds)
        ->not->toContain($inactive->id);
});

it('creates a video that is already in the published scope', function () {
    $video = Video::factory()->create();

    $published = Video::query()
        ->published()
        ->whereKey($video->getKey())
        ->exists();

    expect($published)->toBeTrue();
});

it('generates a tag slug from its translated name', function () {
    $tag = Tag::factory()->create(['name' => 'Service Container']);

    expect($tag->getTranslation('slug', 'en'))->toBe('service-container');
});
