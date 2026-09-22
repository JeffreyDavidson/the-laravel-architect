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

## Deployment safeguards

Run `npm run test:deployment` with Node 22 to test the deployment helpers and the actual command entry point. CI and the pre-push hook run the same command. The CLI tests launch a separate Node process with synthetic credentials and replace the HTTP transport; they do not contact Forge or Cloudflare or require secrets.

Staging must use the combined `deploy` operation: it validates the exact Forge target before making requests and requires a different deployment ID even when redeploying the same commit. Regression coverage includes wrong-site rejection, same-revision timeout, and the workflow's use of that guarded entry point.

Contact integration tests use the real test database queue and inject failure at each notification insert. They verify that no inquiry or job remains after failure and that a retry creates exactly one inquiry and two jobs. Publication tests cover past, current, future, and missing dates, including dashboard counts and linked results. Admin browser tests exercise appearance controls, hit-test the open account menu against underlying content, and check the collapsed create action's alignment and navigation.

Keep command-level coverage for credential forwarding, GET requests, missing credentials, login redirects, HTTP errors, curl failures, exit codes, and secret-safe output. Helper-only tests cannot catch argument mismatches in the command dispatcher. A successful preflight requires HTTP 200; it does not replace the deployed-revision verification or live staging smoke checks.
