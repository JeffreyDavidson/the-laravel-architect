# Project agent guidance

## Frontend styling

- Use Tailwind utilities directly in Blade for layout, typography, colors, spacing, responsive behavior, and interaction states. Do not introduce custom component CSS classes for styling that utilities can express.
- Reuse Blade components when utility-heavy markup repeats. Keep custom CSS for font/theme declarations, keyframes, third-party integration, or behavior that genuinely cannot be expressed clearly with utilities.
- Preserve JavaScript hooks independently from styling, preferably with data attributes.

## Controllers

- Use singular resource controller names, such as `ProjectController`.
- Application controllers are standalone classes; do not reintroduce an empty shared base controller.
- Page ViewModels own SEO metadata and the `seoSource` view payload. Controllers inject the appropriate ViewModel; keep request handling and visibility checks in controllers.
- Controllers must not contain private methods. Keep controllers focused on translating HTTP requests and responses, and move supporting behavior into an appropriately named action, query, builder, or other cohesive application boundary.

## Actions

- Actions in `App\Actions` expose a public, non-static `handle()` method, never `__invoke()`. Call actions explicitly with `->handle(...)`.

## Data transfer objects

- Native `final readonly` classes in `app/Data` describe data crossing application boundaries. They do not fetch data, send mail, or depend on HTTP requests.
- `StoreContactRequest::toData()` maps validated input into `ContactMessageData`, retaining `ContactType` and nullable `ContactBudget` enums. The controller calls it only after honeypot, rate-limit, and Turnstile checks. `SendContactMessage::handle()` accepts that DTO and converts enums to their existing string values at the mailable boundary. Existing queued-mail fields and rendered content remain unchanged.
- `YouTubeService` normalizes external responses into `YouTubeVideoData` objects. The sync command reads typed properties and uses the explicit `toArray()` mapping for new records. Updates continue to preserve curated slugs, publication dates, and featured status. Missing optional API fields remain null; malformed statistics retain the existing zero fallback.
- Simple newsletter arguments, ViewModel payloads, and YouTube's separate statistics arrays remain unchanged. Do not add DTOs automatically for every array or introduce a shared DTO base class.

## Testing

- Keep structural controller rules in `tests/Architecture`; HTTP controller behavior belongs in `tests/Feature/Http/Controllers`.
- Use `jasonmccreary/double` for test doubles. Do not introduce direct Mockery mocks or Laravel facade spies; swap a Double-backed contract into the container or facade instead.
- Prefer Pest expectation chaining as recommended by `Pest\Rector\Rules\ChainExpectCallsRector`, including `->and()` for different values. This overrides the global preference for separate method-call lines where it conflicts with the rule. Do not disable expectation chaining solely to preserve existing test formatting; use Pint for final formatting and preserve test behavior.

## Releases

- After a verified release, synchronize `develop` directly to `main` with a fast-forward-only merge and push. Never open a downstream pull request from `main` into `develop`.
- Never squash, rebase, create a merge commit, or force-push while synchronizing `develop` after a release.
- If branch protection blocks the direct synchronization, temporarily relax only the required-pull-request rule, restore it immediately after the push, and verify the protection is active again.

## Post-merge synchronization and cleanup

- After a verified PR merge into `develop`, include local synchronization and merged-branch cleanup in the workflow without waiting for a separate request.
- Verify the PR is merged on GitHub; do not infer completion from a user message. With a clean working tree, switch to `develop` and pull `origin develop` with `--ff-only`. Stop if local changes, divergence, or another worktree prevent this safely.
- Clean up the merged PR's local and remote head branches only after verifying each existing tip exactly matches the PR's merged head commit. For an explicit broader cleanup request, apply the same checks to every candidate. Squash merges require PR evidence, not just `git branch --merged`.
- Never delete `main`, `develop`, branches with post-merge commits, or branches checked out in another worktree. Do not remove worktrees or discard uncommitted changes as part of cleanup.
- Prefer normal local branch deletion; force-delete a local squash-merged branch only after the checks above prove its work is merged. Verify remote tips again before deletion and use an expected-tip guard where supported.
- Treat a request to merge as authorization for this verified cleanup, subject to execution-policy restrictions. Never bypass a denied operation; report what remains blocked. This file does not override tool permissions or production-operation confirmation requirements.
- Verify the synchronized branch matches its remote and report synchronization, deleted branches, and any skipped or blocked cleanup. Follow the release-specific rules above when the PR targets `main`.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
