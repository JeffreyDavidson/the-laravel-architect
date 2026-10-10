<?php

use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Videos\VideoResource;
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
use App\Providers\AppServiceProvider;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\CategoryResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\ContactInquiries\ContactInquiryResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\EpisodeResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\SocialProfiles\SocialProfileResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Subscribers\SubscriberResource;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

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

dataset('panel resources', [
    CategoryResource::class,
    ContactInquiryResource::class,
    EpisodeResource::class,
    NewsletterIssueResource::class,
    PodcastResource::class,
    PostResource::class,
    ProjectResource::class,
    SocialProfileResource::class,
    SubscriberResource::class,
    TagResource::class,
    VideoResource::class,
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

// Filament allows a resource ability that has no policy unless a gate before-callback
// returns false, so this fails if the gate ever answers null for non-administrators.
it('denies non-administrators every Filament resource ability', function (string $resource) {
    if (! is_subclass_of($resource, Resource::class)) {
        throw new RuntimeException("Unknown panel resource {$resource}.");
    }
    actingAs(User::factory()->create(['is_admin' => false]));
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $model = $resource::getModel();
    $record = new $model;

    $decisions = [
        'viewAny' => $resource::canViewAny(),
        'create' => $resource::canCreate(),
        'deleteAny' => $resource::canDeleteAny(),
        'view' => $resource::canView($record),
        'edit' => $resource::canEdit($record),
        'delete' => $resource::canDelete($record),
    ];

    expect($decisions)
        ->toBe(array_fill_keys(['viewAny', 'create', 'deleteAny', 'view', 'edit', 'delete'], false));
})->with('panel resources');

it('rejects lazy loading outside production', function () {
    Category::factory()
        ->count(2)
        ->create();

    $category = Category::query()
        ->get()
        ->firstOrFail();

    expect(fn () => $category->posts)
        ->toThrow(LazyLoadingViolationException::class);
});

it('refuses destructive database commands and permits lazy loading in production', function () {
    // The guards are static; the next test's fresh application boot re-applies the testing settings.
    app()->instance('env', 'production');
    new AppServiceProvider(app())
        ->boot();

    $this->artisanCommand('db:wipe', ['--force' => true])
        ->assertFailed();

    expect(Schema::hasTable('users'))
        ->toBeTrue()
        ->and(Model::preventsLazyLoading())
        ->toBeFalse();
});
