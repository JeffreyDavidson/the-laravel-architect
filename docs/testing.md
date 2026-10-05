# Test organization

Tests are grouped by the boundary they exercise, not by the class being tested:

- `Unit` tests cover one class or a small pure boundary with in-memory inputs. They use PHPUnit's framework-free base case and do not boot Laravel or use the database, HTTP kernel, browser, queue, mail, or external services.
- `Integration` tests verify multiple application services working together, commonly with the database, filesystem, queue, mail, or framework integrations. They call application classes directly rather than entering through an HTTP or console boundary. Keep them organized under the application boundary they integrate, such as `Models`, `Jobs`, `Actions`, `Queries`, `ViewModels`, or `Http/Requests`.
- `Feature` tests enter through an application boundary such as an HTTP route, Artisan command, seeder, health check, middleware, or Livewire component and assert the resulting behavior. Keep them under a responsibility directory such as `Http/Controllers`, `Console/Commands`, `Database/Seeders`, `Filament`, or `Models`; the root `Feature` directory should remain empty. Configuration invariants that do not enter an application boundary belong under `Unit/Configuration` or `Integration/Configuration`.
- `Browser` tests are PHP tests that drive a real browser with Pest Browser. They may arrange application state, but their assertions must be observable through the rendered page, navigation, browser JavaScript, accessibility tree, or layout. Organize them by user-facing capability (such as `Accessibility` or `Projects`) rather than by Laravel controller. Reusable page navigation belongs in `Browser/Pages`; reusable UI interactions belong in `Browser/Components`. Use plain PHP helpers and add an abstraction only when it removes repeated selectors or workflows.
- Browser tests use Pest Browser for local application behavior, responsive checks, accessibility, asset loading, and JavaScript interactions. Production and staging smoke checks use Pest tests in `Feature/Http/ProductionSmokeTest.php` with `PRODUCTION_BASE_URL` set by the workflow.
- `Architecture` tests enforce source-level project conventions and do not belong in the behavioral suites.

Every class in `app/Console/Commands` must have exactly one matching test file in `Feature/Console/Commands` or `Integration/Console/Commands`. Framework scheduler tests are separate under `Feature/Console/Scheduling`.

When a test mixes boundaries, keep the test at the highest boundary that is actually under test. For example, a controller response belongs in `Feature`, while the same page's keyboard navigation or rendered layout belongs in `Browser`; a direct action/database workflow belongs in `Integration`; and a FormRequest tested through Laravel's validator belongs in `Integration/Http/Requests`. A request submitted through a route belongs in `Feature`.

Browser page objects own page URLs and page-level navigation; components own reusable UI interactions; test files own the scenario and its observable assertions. Prefer accessible names, roles, labels, and stable `data-*` hooks over selectors tied to CSS structure.

The PHP suites are registered in `phpunit.xml` and executed through Pest. Pest Browser uses the repository's Playwright package and Chromium installation.

## Composer scripts

These names are shared with the mouse28 repository; keep them identical when changing either.

| Script | Runs |
| --- | --- |
| `composer check` | Every gate in CI order: Composer validate and audit, npm audit, deployment-helper tests, `test:lint`, method-chain check, frontend formatting, `test:filament`, `test:types`, `test:types:pest`, `test:rector`, `test:rector:pest`, `test`, `test:type-coverage`, asset build, asset budgets, then `test:browser` |
| `composer lint` / `composer test:lint` | Pint (Blade included): fix / check only |
| `composer rector` / `composer rector:pest` | Rector fixes for the application / Pest configuration |
| `composer test:rector` / `composer test:rector:pest` | Rector dry runs |
| `composer test:types` / `composer test:types:pest` | PHPStan for the application / tests (`phpstan-pest.neon`, `APP_ENV=testing`) |
| `composer test:filament` | Filacheck |
| `composer test` | Pest in parallel, excluding the Browser suite, failing on risky tests |
| `composer test:browser` | The Browser suite |
| `composer test:architecture` | The Architecture suite |
| `composer test:type-coverage` | Pest type coverage at a 100% minimum; CI enforces it after the non-browser tests |

The pre-push hook runs `test`, `test:browser`, `test:types`, and `test:rector` together with the deployment-helper tests.

## Isolated test environment

`phpunit.xml` pins every driver and external credential with both `<server>` and `<env force="true">`, because Laravel also reads inherited server variables and an unforced `<env>` can be overridden by `.env` or the shell. Tests always use an in-memory SQLite database, the array mailer, array cache and sessions, and the sync queue. Credentials for Resend, Turnstile, YouTube, Sentry, Nightwatch, the backup destinations, and S3 are blank, and Nightwatch is disabled. `TestCase` calls `Http::preventStrayRequests()`, so every outgoing request must be faked. `tests/Integration/TestHarnessTest.php` guards these guarantees; the same pattern is used in the mouse28 repository. When adding an external service, pin its credentials in `phpunit.xml` and add them to that test.

## Query budgets

`tests/Feature/Http/Controllers/PublicPageQueryBudgetsTest.php` seeds the scale-test content (100 posts, 50 projects, 300 episodes) plus 30 tagged posts, 30 newsletter issues, and 30 videos, then asserts the exact SQL query count for each public page with `expectsDatabaseQueryCount()`. No page's count grows with its content, so a new N+1 or an added query fails the test. Lazy loading already throws outside production; the budgets also catch explicit extra queries. The same pattern is used in the mouse28 repository.

| Page | Queries | Page | Queries |
| --- | --- | --- | --- |
| Home | 12 | Projects index | 4 |
| Blog index | 7 | Project | 8 |
| Post | 11 | Newsletter index | 4 |
| Category | 7 | Newsletter issue | 5 |
| Tag | 7 | Archive | 5 |
| Podcast index | 3 | Search | 13 |
| Podcast | 6 | About | 2 |
| Episode | 11 | Contact | 4 |

Search runs one count query per result group for its per-group pagination, and skips the results query for groups with no matches.

To change a budget intentionally, make the change, run the test, and confirm the new count in the failure message is constant for the page (it must not depend on the amount of content). Update the dataset value and this table in the same commit, and explain the new query in the PR. Never raise a budget to absorb an N+1: eager-load the relation instead.

## Deployment safeguards

`php scripts/check-method-chaining.php` requires each chained method call on its own line: a line fails when an `->` continues a method call made earlier on the same line, such as `$query->where()->first()`. Separate accesses such as `$this->save($model->id)`, enum `->value`, property chains, and `->not` are allowed, and merged migrations are not checked. CI runs it across the repository; the pre-push hook runs it on changed PHP files. `tests/Unit/Scripts/CheckMethodChainingTest.php` covers what it flags and allows.

Run `npm run test:deployment` with Node 22 to test the deployment helpers and the actual command entry point. CI and the pre-push hook run the same command. The CLI tests launch a separate Node process with synthetic credentials and replace the HTTP transport; they do not contact Forge or Cloudflare or require secrets.

Staging must use the combined `deploy` operation: it validates the exact Forge target before making requests and requires a different deployment ID even when redeploying the same commit. Regression coverage includes wrong-site rejection, same-revision timeout, and the workflow's use of that guarded entry point. It also covers read-only requests retrying timeouts and HTTP 502-504 up to three attempts, polling continuing through read errors until the deadline, and the Forge trigger being attempted exactly once.

Contact integration tests use the real test database queue and inject failure at the email job insert. They verify that no inquiry or job remains after failure and that a retry creates exactly one inquiry and one job. Job tests cover per-email stamps, retrying only the failed email, cancelled sends, the 23-hour manual-review cutoff and stable idempotency keys. Publication tests cover past, current, future, and missing dates, including dashboard counts and linked results. Admin browser tests exercise appearance controls, hit-test the open account menu against underlying content, and check the collapsed create action's alignment and navigation.

Keep command-level coverage for credential forwarding, GET requests, missing credentials, login redirects, HTTP errors, curl failures, exit codes, and secret-safe output. Helper-only tests cannot catch argument mismatches in the command dispatcher. A successful preflight requires HTTP 200; it does not replace the deployed-revision verification or live staging smoke checks.

## Local content and scale checks

The default `db:seed` creates the local administrator only. For representative
public editorial content, use a reviewed public-content archive; the archive
contains text and metadata but not uploaded media, and importing it replaces
the target's current public content. For repeatable listing and pagination
checks, run `php artisan content:scale-test seed`, then remove its prefixed
records with `php artisan content:scale-test clear`. This creates one podcast,
300 episodes, 100 posts, and 50 projects without audio or uploaded files.

Compare the same `/blog`, `/podcasts/scale-test-podcast`, and `/projects`
requests before and after seeding. The podcast show route exercises its episode
listing; `/podcasts` is the podcast index. Record response timings, query count
and duration, and memory use; repeat requests to distinguish first-request cost
from steady-state behavior.
For a repeatable local application-level sample, seed the scale-test content and
run `php artisan content:benchmark --iterations=5`. The command measures those
three routes through Laravel's HTTP kernel, reports first-request metrics and
the average of subsequent requests, and makes no content changes. It is limited
to `APP_ENV=local`; it does not measure the Herd/PHP-FPM/network overhead or
production cache behavior.
Use staging only for an approved measurement window, with
`content:scale-test seed --staging` and `content:scale-test clear --staging`:
its public pages will show the synthetic published records until cleanup runs.
Do not add public-page caching until measurements show a material benefit and
its invalidation behavior has been designed and verified.
