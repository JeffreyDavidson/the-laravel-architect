---
paths:
  - 'tests/**'
---

# Tests

## Keep test structure and doubles consistent
Keep structural rules in tests/Architecture and HTTP controller behavior in tests/Feature/Http/Controllers. Use jasonmccreary/double for test doubles and prefer Pest expectation chaining.

## Use project test doubles
Use jasonmccreary/double for test doubles; do not introduce direct Mockery mocks or Laravel facade spies. Swap a Double-backed contract into the container or facade instead.

## Separate tests by execution boundary
Unit tests exercise isolated logic without booting Laravel. Integration tests boot Laravel to verify an application class with its real collaborators, persistence, or configuration. Feature tests exercise HTTP, Artisan, Livewire, and Filament entry points; a database alone does not make a test a Feature test. Browser tests cover real-browser behavior, and Architecture tests cover source contracts. Mirror the owning app class path within Unit, Integration, and Feature, and preserve assertions and dataset cases when moving coverage.

## Keep Unit tests isolated
Unit tests extend PHPUnit's TestCase: construct the class under test directly, pass synthetic inputs, and assert its returned value or thrown exception. Do not use factories, facades, the database, or HTTP clients. Script tests may run the script in a subprocess against a temporary file.

## Test user workflows through entry points
Put public HTTP response coverage with the controller that owns the route and Artisan coverage with the owning command. Livewire components and Filament pages are entry points: keep their authorization, validation, state changes, redirects, and rendered output in Feature tests, and test the underlying models, actions, and support classes at their narrower Integration or Unit boundary.

## Test collaborating services directly in Integration
Integration tests exercise a concrete application class with deterministic, synthetic fixtures. Fake outbound HTTP before resolving the class under test. Keep a focused assertion when a class intentionally selects reduced columns, caches within a request, or branches on configuration. Do not add query-count assertions to ordinary cases; page query budgets live in `tests/Feature/Http/Controllers/PublicPageQueryBudgetsTest.php`.

## Keep Browser tests at the browser boundary
Browser tests cover behavior that only exists in a rendered browser: interaction, JavaScript, keyboard and focus, responsive layout, accessibility, and asset loading. Keep response shape, persistence, validation, authorization, and query behavior in Feature or Integration tests. Split unrelated interactions into separate tests and assert what a user can observe.

## Name tests for the behavior they assert
Name each test for the complete behavior it verifies, after the class that owns that behavior rather than a model used as a fixture. Split a test that covers unrelated behaviors into focused cases.

## Use the smallest effective fixture
Create only the records and files needed to cross the behavior boundary under test; for pagination, create just enough to reach the next page. Set only factory attributes that affect the behavior or are asserted directly.

## Put each call in a method chain on its own line
composer check runs scripts/check-method-chaining.php, which fails a line when an arrow continues a chain whose previous link is a method call on the same line. In tests this catches `$post->refresh()->episode_id`, `expect($x)->and(Model::query()->whereKey($id)->exists())` and `$collection->pluck('a')->sort()->all()`. Put one call per line, or assign the intermediate result to a variable and then assert on it. Property chains and `->value` are allowed. Merged migrations are not checked.

## Keep Pest tests free of mixed types for PHPStan
phpstan-pest.neon fails on mixed values. Add an `@var array<string, mixed> $param` docblock for dataset parameters that need it, use `->pluck('id')->all()` instead of a closure that returns `$model->getKey()` for an int, and narrow with `instanceof` before using a value as a model or request. Fix the type, do not widen a parameter or return type just to silence the error.
