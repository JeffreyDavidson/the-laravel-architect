<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

it('seeds repeatable published scale-test content', function () {
    $firstSeed = $this->artisanCommand('content:scale-test', ['action' => 'seed']);
    $firstSeed->assertSuccessful();
    $firstSeed->run();

    $secondSeed = $this->artisanCommand('content:scale-test', ['action' => 'seed']);
    $secondSeed->assertSuccessful();
    $secondSeed->run();

    $this->assertDatabaseCount('podcasts', 1);
    $this->assertDatabaseCount('episodes', 300);
    $this->assertDatabaseCount('posts', 100);
    $this->assertDatabaseCount('projects', 50);
    $this->assertDatabaseHas('users', ['email' => 'scale-test-author@example.invalid']);

    $publishedEpisodes = DB::table('episodes');
    $publishedEpisodes = $publishedEpisodes->where('status', 'published');
    $publishedEpisodeCount = $publishedEpisodes->count();

    expect($publishedEpisodeCount)->toBe(300);
});

it('leaves synthetic content out of the default database seeder', function () {
    $databaseSeeder = $this->artisanCommand('db:seed');
    $databaseSeeder->assertSuccessful();
    $databaseSeeder->run();

    $this->assertDatabaseMissing('podcasts', ['slug' => 'scale-test-podcast']);
    $this->assertDatabaseMissing('episodes', ['slug' => 'scale-test-episode-001']);
    $this->assertDatabaseMissing('posts', ['slug' => 'scale-test-post-001']);
    $this->assertDatabaseMissing('projects', ['slug' => 'scale-test-project-001']);
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

    $now = now();
    DB::table('posts')->insert([
        'title' => 'A real local draft',
        'slug' => 'a-real-local-draft',
        'content' => 'Keep this record.',
        'category_id' => $categoryId,
        'user_id' => $authorId,
        'status' => 'draft',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('projects')->insert([
        'title' => 'A real local project',
        'slug' => 'a-real-local-project',
        'description' => 'Keep this record.',
        'status' => 'draft',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $clear = $this->artisanCommand('content:scale-test', ['action' => 'clear']);
    $clear->assertSuccessful();
    $clear->run();

    $this->assertDatabaseMissing('episodes', ['slug' => 'scale-test-episode-001']);
    $this->assertDatabaseMissing('posts', ['slug' => 'scale-test-post-001']);
    $this->assertDatabaseMissing('projects', ['slug' => 'scale-test-project-001']);
    $this->assertDatabaseMissing('podcasts', ['slug' => 'scale-test-podcast']);
    $this->assertDatabaseHas('posts', ['slug' => 'a-real-local-draft']);
    $this->assertDatabaseHas('projects', ['slug' => 'a-real-local-project']);
    $this->assertDatabaseHas('categories', ['slug' => 'scale-test-category']);
    $this->assertDatabaseHas('users', ['email' => 'scale-test-author@example.invalid']);
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

    $this->assertDatabaseMissing('posts', ['slug' => 'scale-test-post-001']);
});

it('allows scale content on the exact staging host only when explicitly enabled', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('app.url', 'https://staging.thelaravelarchitect.com');

    $stagingSeed = $this->artisanCommand('content:scale-test', ['action' => 'seed', '--staging' => true]);
    $stagingSeed->assertSuccessful();
    $stagingSeed->run();

    $this->assertDatabaseCount('posts', 100);
    $this->assertDatabaseCount('projects', 50);
});

it('rejects unsupported actions before changing content', function () {
    $invalidAction = $this->artisanCommand('content:scale-test', ['action' => 'delete-everything']);
    $invalidAction->expectsOutput('The action must be either seed or clear.');
    $invalidAction->assertFailed();
    $invalidAction->run();

    $this->assertDatabaseMissing('posts', ['slug' => 'scale-test-post-001']);
});
