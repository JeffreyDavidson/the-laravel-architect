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
