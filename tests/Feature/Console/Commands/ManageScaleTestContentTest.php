<?php

use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

pest()->use(RefreshDatabase::class);

it('seeds repeatable published scale-test content', function () {
    $firstSeed = $this->artisanCommand('content:scale-test', ['action' => 'seed']);
    $firstSeed->assertSuccessful();
    $firstSeed->run();

    $secondSeed = $this->artisanCommand('content:scale-test', ['action' => 'seed']);
    $secondSeed->assertSuccessful();
    $secondSeed->run();

    assertDatabaseCount('podcasts', 1);
    assertDatabaseCount('episodes', 300);
    assertDatabaseCount('posts', 100);
    assertDatabaseCount('projects', 50);
    assertDatabaseHas('users', ['email' => 'scale-test-author@example.invalid']);

    $publishedEpisodes = DB::table('episodes');
    $publishedEpisodes = $publishedEpisodes->where('status', 'published');
    $publishedEpisodeCount = $publishedEpisodes->count();

    expect($publishedEpisodeCount)->toBe(300);
});

it('leaves synthetic content out of the default database seeder', function () {
    $databaseSeeder = $this->artisanCommand('db:seed');
    $databaseSeeder->assertSuccessful();
    $databaseSeeder->run();

    assertDatabaseMissing('podcasts', ['slug' => 'scale-test-podcast']);
    assertDatabaseMissing('episodes', ['slug' => 'scale-test-episode-001']);
    assertDatabaseMissing('posts', ['slug' => 'scale-test-post-001']);
    assertDatabaseMissing('projects', ['slug' => 'scale-test-project-001']);
});

it('clears only synthetic records and retains shared owners that still have real content', function () {
    $seed = $this->artisanCommand('content:scale-test', ['action' => 'seed']);
    $seed->assertSuccessful();
    $seed->run();

    $authorQuery = DB::table('users');
    $authorQuery = $authorQuery->where('email', 'scale-test-author@example.invalid');
    $authorId = $authorQuery->value('id');

    $categoryQuery = DB::table('categories');
    $categoryQuery = $categoryQuery->where('slug', 'scale-test-category');
    $categoryId = $categoryQuery->value('id');

    Post::factory()->create([
        'slug' => 'a-real-local-draft',
        'category_id' => $categoryId,
        'user_id' => $authorId,
    ]);
    Project::factory()->create(['slug' => 'a-real-local-project']);

    $clear = $this->artisanCommand('content:scale-test', ['action' => 'clear']);
    $clear->assertSuccessful();
    $clear->run();

    assertDatabaseMissing('episodes', ['slug' => 'scale-test-episode-001']);
    assertDatabaseMissing('posts', ['slug' => 'scale-test-post-001']);
    assertDatabaseMissing('projects', ['slug' => 'scale-test-project-001']);
    assertDatabaseMissing('podcasts', ['slug' => 'scale-test-podcast']);
    assertDatabaseHas('posts', ['slug' => 'a-real-local-draft']);
    assertDatabaseHas('projects', ['slug' => 'a-real-local-project']);
    assertDatabaseHas('categories', ['slug' => 'scale-test-category']);
    assertDatabaseHas('users', ['email' => 'scale-test-author@example.invalid']);
});

it('rejects scale content writes on production hosts and production environments without staging permission', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('app.url', 'https://thelaravelarchitect.com');

    $productionSeed = $this->artisanCommand('content:scale-test', ['action' => 'seed', '--staging' => true]);
    $productionSeed->expectsOutput('Scale-test content may only be changed outside production.');
    $productionSeed->assertFailed();
    $productionSeed->run();

    config()->set('app.url', 'https://staging.thelaravelarchitect.com');

    $unapprovedStagingSeed = $this->artisanCommand('content:scale-test', ['action' => 'seed']);
    $unapprovedStagingSeed->expectsOutput('Scale-test content may only be changed outside production.');
    $unapprovedStagingSeed->assertFailed();
    $unapprovedStagingSeed->run();

    assertDatabaseMissing('posts', ['slug' => 'scale-test-post-001']);
});

it('allows scale content on the exact staging host only when explicitly enabled', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('app.url', 'https://staging.thelaravelarchitect.com');

    $stagingSeed = $this->artisanCommand('content:scale-test', ['action' => 'seed', '--staging' => true]);
    $stagingSeed->assertSuccessful();
    $stagingSeed->run();

    assertDatabaseCount('posts', 100);
    assertDatabaseCount('projects', 50);
});

it('rejects unsupported actions before changing content', function () {
    $invalidAction = $this->artisanCommand('content:scale-test', ['action' => 'delete-everything']);
    $invalidAction->expectsOutput('The action must be either seed or clear.');
    $invalidAction->assertFailed();
    $invalidAction->run();

    assertDatabaseMissing('posts', ['slug' => 'scale-test-post-001']);
});
