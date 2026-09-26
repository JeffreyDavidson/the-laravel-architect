<?php

use App\Models\Category;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Laravel\Nightwatch\Core;

pest()->use(RefreshDatabase::class);

it('registers pseudonymous authenticated user details with Nightwatch', function () {
    Config::set('app.key', 'private-application-key');
    $resolver = app(Core::class)->userDetailsResolver;
    if ($resolver === null) {
        throw new RuntimeException('Nightwatch user details resolver was not registered.');
    }
    $user = new User;
    $user->forceFill([
        'id' => 42,
        'name' => 'Private Administrator',
        'email' => 'private@example.test',
    ]);

    expect($resolver($user))->toBe([
        'id' => hash_hmac('sha256', '42', 'private-application-key'),
    ]);
});

it('rejects lazy loading outside production', function () {
    Category::query()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    Category::query()->create(['name' => 'Architecture', 'slug' => 'architecture']);

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
