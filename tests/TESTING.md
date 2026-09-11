# Test organization

Tests are grouped by the boundary they exercise, not by the class being tested:

- `Unit` tests cover one class or a small pure boundary with in-memory inputs. They use PHPUnit's framework-free base case and do not boot Laravel or use the database, HTTP kernel, browser, queue, mail, or external services.
- `Integration` tests verify multiple application services working together, commonly with the database, filesystem, queue, mail, or framework integrations. They call application classes directly rather than entering through an HTTP or console boundary. Keep them organized under the application boundary they integrate, such as `Models`, `Jobs`, `Actions`, `Queries`, `ViewModels`, or `Http/Requests`.
- `Feature` tests enter through an application boundary such as an HTTP route, Artisan command, seeder, health check, middleware, or Livewire component and assert the resulting behavior. Keep them under a responsibility directory such as `Http/Controllers`, `Console/Commands`, `Database/Seeders`, `Filament`, or `Models`; the root `Feature` directory should remain empty. Configuration invariants that do not enter an application boundary belong under `Unit/Configuration` or `Integration/Configuration`.
- `Browser` tests are PHP tests that drive a real browser with Pest Browser. They may arrange application state, but their assertions must be observable through the rendered page, navigation, browser JavaScript, accessibility tree, or layout. Organize them by user-facing capability (such as `Accessibility` or `Projects`) rather than by Laravel controller. Reusable page navigation belongs in `Browser/Pages`; reusable UI interactions belong in `Browser/Components`. Use plain PHP helpers and add an abstraction only when it removes repeated selectors or workflows.
- `e2e` tests are Playwright tests in TypeScript. They run against the application over HTTP and cover cross-page, responsive, accessibility, asset-loading, or production smoke behavior. Organize them by responsibility under `e2e/pages`, `e2e/interactions`, `e2e/responsive`, `e2e/accessibility`, and `e2e/assets`; reusable page models belong in `e2e/pages`, reusable UI behavior in `e2e/components`, and shared assertions/helpers in `e2e/support`. Production checks remain under `e2e/production` and use their separate Playwright config.
- `Architecture` tests enforce source-level project conventions and do not belong in the behavioral suites.

Every class in `app/Console/Commands` must have exactly one matching test file in `Feature/Console/Commands` or `Integration/Console/Commands`. Framework scheduler tests are separate under `Feature/Console/Scheduling`.

When a test mixes boundaries, keep the test at the highest boundary that is actually under test. For example, a controller response belongs in `Feature`, while the same page's keyboard navigation or rendered layout belongs in `Browser`; a direct action/database workflow belongs in `Integration`; and a FormRequest tested through Laravel's validator belongs in `Integration/Http/Requests`. A request submitted through a route belongs in `Feature`.

Browser page objects own page URLs and page-level navigation; components own reusable UI interactions; test files own the scenario and its observable assertions. Prefer accessible names, roles, labels, and stable `data-*` hooks over selectors tied to CSS structure.

The PHP suites are registered in `phpunit.xml`. Playwright suites are intentionally not PHP test suites and are run with `npm run test:e2e` or `npm run test:e2e:production`.
