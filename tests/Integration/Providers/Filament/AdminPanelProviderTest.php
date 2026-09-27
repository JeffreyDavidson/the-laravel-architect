<?php

use App\Models\Category;
use App\Models\ContactInquiry;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\SocialProfile;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Gate;

const CLASS_ABILITIES = ['viewAny', 'create', 'deleteAny', 'restoreAny', 'forceDeleteAny', 'reorder'];

const RECORD_ABILITIES = ['view', 'update', 'delete', 'restore', 'forceDelete', 'replicate'];

dataset('panel models', [
    Category::class,
    ContactInquiry::class,
    Episode::class,
    NewsletterIssue::class,
    Podcast::class,
    Post::class,
    Project::class,
    SocialProfile::class,
    Subscriber::class,
    Tag::class,
    Video::class,
]);

/**
 * Every ability Filament checks for a model, mapped to whether the user may perform it.
 *
 * @return array<string, bool>
 */
function panelAbilityDecisions(User $user, string $model): array
{
    if (! class_exists($model)) {
        throw new RuntimeException("Unknown panel model {$model}.");
    }

    $gate = Gate::forUser($user);
    $record = new $model;
    $decisions = [];

    foreach (CLASS_ABILITIES as $ability) {
        $decisions[$ability] = $gate->allows($ability, $model);
    }

    foreach (RECORD_ABILITIES as $ability) {
        $decisions[$ability] = $gate->allows($ability, $record);
    }

    return $decisions;
}

it('allows the administrator every panel ability', function (string $model) {
    $administrator = User::factory()
        ->make(['is_admin' => true]);

    $decisions = panelAbilityDecisions($administrator, $model);

    expect($decisions)
        ->toBe(array_fill_keys([...CLASS_ABILITIES, ...RECORD_ABILITIES], true));
})->with('panel models');

it('denies non-administrators every panel ability', function (string $model) {
    $user = User::factory()
        ->make(['is_admin' => false]);

    $decisions = panelAbilityDecisions($user, $model);

    expect($decisions)
        ->toBe(array_fill_keys([...CLASS_ABILITIES, ...RECORD_ABILITIES], false));
})->with('panel models');
