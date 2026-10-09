# Layers: where code goes

Every class in `app/` belongs to one layer, and each layer has a short list of
things it never does. This page is the map for people and AI agents adding code.
Read it before you create a class, then read the matching file in `.ai/rules`
(see [the rules index](../../.ai/rules/index.md)) for the details.

Since 2026-10-08 the security headers, Turnstile, runtime health, monitoring
redaction, feed renderers and Markdown renderer live in the private
`jeffreydavidson/creator-kit` package, and since 2026-10-09 so does the
publishing core (`PublishStatus`, the publishing concerns, `PublishContent`,
`ContentNotReadyToPublish`, the publish actions and form components, and
`DisplayTimezone`), and the SEO building blocks (`PageViewModel`, `PageMeta`,
`AppliesStoredSeo`, `JsonLd`, `CollectionListing`, `PaginatedPageSeo`,
`TextSearch`, `SeoSection`) and the responsive-image services and `media:*`
commands (`ResponsiveImageVariants`, `StoredMediaLifecycle`, the image
workflows), and the Categories and Social Profiles admin screens with
`SlugSourceInput` (registered through `CreatorKitPlugin` in `AdminPanelProvider`),
and the newsletter's actions, job, middleware, controllers, enums, presenter
and routes; where this page names them as examples, they now come from
the package rather than `app/`.

The rules come from the 2026-10-07 architecture audit and are locked in by Pest
`arch()` tests in `tests/Architecture`. Where a layer has no test yet, the
"Enforced by" column says so: the rule still applies, but nothing catches a slip.

## Two Rules for Every File

- Start the file with `declare(strict_types=1);` and make the class `final`.
  Leave a class open only for a test double or subclass, and list it with the
  reason in `tests/Architecture/ConventionsArchitectureTest.php`. Mark a final
  class `readonly` only when it holds no state.
- Dependencies point down the request lifecycle below. A Query never knows
  about HTTP, a Model never knows about URLs, and Support knows nothing about
  this site.

## The Request Lifecycle

Take `GET /archive?type=writing`:

1. **Route** (`routes/web.php`) names the URL `archive.index` and attaches its
   middleware. Throttles, `signed`, `cache.headers` and session stripping
   belong on the route, not in the controller.
2. **Middleware** runs the cross-cutting HTTP work. `AddSecurityHeaders` is
   appended globally in `bootstrap/app.php`.
3. **FormRequest** (`ArchiveIndexRequest`) trims and validates the query string.
   A bad public filter is a 404, not a validation error page.
4. **Controller** (`ArchiveController`, invokable) passes the validated values to
   its injected ViewModel and returns the view. That's the whole method.
5. **ViewModel** (`ArchiveViewModel`) calls `ArchiveQuery`, adds paginator links,
   answers the out-of-range 404, maps each result to display data with URLs and
   builds the page's `PageMeta` (SEO plus JSON-LD).
6. **Query** returns records or DTOs (`ContentListItem`); a **Presenter** turns
   one model into display values and URLs when the page lists models.
7. **Blade** (`pages/archive.blade.php` inside `components/layouts/site`) renders
   the data. The layout reads SEO and JSON-LD from `pageMeta` only.

A write follows the same front half and then branches: `POST /contact` goes
route → `StoreContactRequest` (validation, Turnstile rule, honeypot,
`toData()`) → `ContactController::store` → `SendContactMessage` Action (one
transaction, dispatches the `SendContactInquiryEmails` Job) → redirect.

## HTTP Entry Points

| Layer | Does | Never does | Enforced by |
| --- | --- | --- | --- |
| **Route**<br>`routes/web.php`, `routes/console.php` | URL, dotted route name (`blog.show`), every middleware (throttle, `signed`, `cache.headers`, `withoutMiddleware('web')`), slug binding (`{post:slug}`) | Logic or data loading | Feature tests per controller in `tests/Feature/Http/Controllers` |
| **Middleware**<br>`app/Http/Middleware`, `Add…`/`Ensure…` | Cross-cutting HTTP: security headers, signed-link checks (`EnsureValidNewsletterConfirmationLink`) | Page data or business decisions | Feature tests in `tests/Feature/Http/Middleware` (no arch test) |
| **FormRequest**<br>`app/Http/Requests`, named after the input (`StoreContactRequest`, `ArchiveIndexRequest`) | Normalise and validate every input, GET filters included; public filters 404 on bad input; rule objects from `app/Rules`; map to a DTO with `toData()` | Saving, sending, or reads beyond what validation needs | No arch test (`.ai/rules/controllers.md`) |
| **Controller**<br>`app/Http/Controllers`, singular `{Resource}Controller` | Publish guard (`abort_unless($post->isPublished(), 404)`), then either validated input → one ViewModel → view, or one Action → redirect/response | Queries, `SEOData`, private helpers, non-resource public methods | `tests/Architecture/ControllerArchitectureTest.php` |
| **Livewire**<br>`app/Livewire` | Interactive state for one component (`BlogIndex`), going through the same page ViewModel | Calling Queries | `tests/Architecture/LivewireArchitectureTest.php` |
| **Command**<br>`app/Console/Commands`, imperative name, no `Command` suffix | Parse input, safety checks, call an Action or Workflow, print the report, choose the exit code | Persistence loops, calling other Artisan commands for a workflow | `tests/Architecture/TestOrganizationTest.php` (naming, one test per command, `#[Signature]`) |

## The View Layer

| Layer | Does | Never does | Enforced by |
| --- | --- | --- | --- |
| **ViewModel**<br>`app/ViewModels`, `{Page}ViewModel`, injected into the controller method | One per page. Injects the Queries it needs, takes validated filters and bound models, adds paginator links and page 404s, returns a typed `data()` (and `previewData()`) array | Formatting one model inline (use its Presenter), SEO under any key but `pageMeta` | `tests/Architecture/ViewModelArchitectureTest.php` |
| **PageViewModel / PageMeta**<br>`app/Contracts/PageViewModel`, `app/Data/PageMeta` | Every page ViewModel implements `PageViewModel` and returns `PageMeta` (its `SEOData` plus its own JSON-LD) under `pageMeta`, documented as `pageMeta: PageMeta`. Content pages apply the admin's saved SEO through `Concerns\AppliesStoredSeo`; previews are `noindex, nofollow` | Site-wide entities: those come from `SiteStructuredData` | `tests/Architecture/ViewModelArchitectureTest.php` (lists the feed ViewModels and `SiteStructuredData` as the only non-page classes) |
| **Presenter**<br>`app/Presenters`, `{Model}Presenter`, built with `XPresenter::from($model)` | Display formatting for one model: dates, durations, images and srcsets, every public, preview, storage and external URL, the model's own schema.org node | Queries, `DB`, writes, `app()`/`route()` or the `Storage`, `URL` and `Vite` facades inside methods (collaborators come through the constructor) | `tests/Architecture/PresenterArchitectureTest.php` |
| **Blade component**<br>`resources/views/components` (anonymous), `app/View/Components` (class) | Repeated markup; data the component owns everywhere it appears (`SocialLinks` calls `SocialProfilesQuery`) | Page-specific data | No arch test (`.ai/rules/views.md`) |
| **Site-wide view data**<br>`app/ViewModels/SiteStructuredData` | The WebSite entity, author reference, fixed-page nodes and breadcrumbs, `@inject`ed by the site layout | Page data | `ViewModelArchitectureTest.php` |

There are no View Composers. Data shared by every page is either a class
component or `SiteStructuredData` injected into the layout; add a composer only
when neither fits, and put it in `app/View/Composers`.

## Reads

| Layer | Does | Never does | Enforced by |
| --- | --- | --- | --- |
| **Query**<br>`app/Queries`, `{WhatItReturns}Query` (`ArchiveQuery`, `RelatedPostsQuery`) | Read only. Applies every filter, ordering, eager load and pagination; returns models, collections, paginators or `app/Data` DTOs. May pick a paginator's page parameter name | Writes, `abort*`, `request()`, `route()`/`url()`, paginator links, `App\Http`, Filament, display formatting | `tests/Architecture/QueryArchitectureTest.php` |
| **Criteria**<br>`…Criteria` beside the rule it mirrors (`app/Publishing`) | Changes a builder passed to it (`apply()`, `whereReady()`), so the same check runs in SQL | Running the query itself or returning results | `tests/Architecture/PublishingArchitectureTest.php`, plus `tests/Integration/Publishing/ContentReadinessCriteriaTest.php` for PHP and SQL parity |

## Writes and Work

| Layer | Does | Never does | Enforced by |
| --- | --- | --- | --- |
| **Action**<br>`app/Actions`, imperative verb (`PublishContent`, `SendContactMessage`), public `handle()` | One state change: owns its transaction and business guards, may call other Actions or dispatch Jobs, throws a named exception from `app/Exceptions` when a rule fails | `__invoke`, HTTP or Filament classes, read-only work (that's a Query plus a Renderer), unbounded loops over whole tables | `tests/Architecture/ActionArchitectureTest.php` |
| **Workflow service**<br>`app/Services`, `…Workflow` | One long operation over many records (`YouTubeVideoSyncWorkflow`, `ResponsiveImageRepairWorkflow`), constructor-injected collaborators, returns a report for the command to print | Taking collaborators as callables; `Synchronizer`, `Manager` or `Processor` names | `tests/Architecture/ServiceArchitectureTest.php` (`.ai/rules/services.md`) |
| **Integration service**<br>`app/Services`, `…Service`, `…Verifier`, `…Monitor` | Wraps an external system or runtime check (`YouTubeService`, `TurnstileVerifier`, `DeploymentVerifier`, `Health\RuntimeHealthMonitor`) | Business decisions | `tests/Architecture/ServiceArchitectureTest.php` (`.ai/rules/services.md`) |
| **Other services**<br>`app/Services` | Shared technical work with state or I/O: `StoredMediaLifecycle`, `ResponsiveImageVariants`, `OgImageCache`, the `ContentArchive` importer and exporter | Page data or HTTP | `tests/Architecture/ConventionsArchitectureTest.php` only |
| **Renderer / Generator**<br>`app/Support/Feeds/…Renderer`; `app/Services/…Generator` when it needs a model | Pure output from the data it's given: RSS, sitemap and robots.txt strings, OG and featured-image PNG bytes | Queries, writing files or rows (the caller stores the result) | `tests/Architecture/SupportArchitectureTest.php` (no models in `Support\Feeds`) |
| **Job**<br>`app/Jobs`, imperative verb (`DeliverNewsletterIssue`) | Async, retryable unit: carries an id or one model, reloads it, re-checks guards, sends or calls an Action or Service, owns retry and idempotency | Business logic beyond retry and idempotency. Actions (or the scheduler in `routes/console.php`) dispatch jobs, not Filament or controllers | `tests/Architecture/JobArchitectureTest.php` (`.ai/rules/services.md`) |
| **Mail / Notification**<br>`app/Mail`, `app/Notifications` | Message content via `envelope()` and `content()` | Deciding whether to send (the Action or Job decides) | `tests/Architecture/MailArchitectureTest.php` (`.ai/rules/mail.md`) |

## The Domain

| Layer | Does | Never does | Enforced by |
| --- | --- | --- | --- |
| **Model**<br>`app/Models`, concerns in `Models/Concerns` | Relationships, casts, `#[Scope]` scopes, state transitions (`publish()`), simple derived state, the stored `HasSEO` row; observers registered with `#[ObservedBy]` | URLs or `route()`/`url()`, `Storage`, Presenters, Services, `App\Publishing`, `SEOData`, file changes in boot hooks, `resolveRouteBinding` | `tests/Architecture/ModelArchitectureTest.php`, `tests/Architecture/PublishingArchitectureTest.php` |
| **Observer**<br>`app/Observers`, `{Model}Observer` | Delegates lifecycle side effects (`StoredMediaLifecycle`, `OgImageCache`) and cascades between models, after commit | `Storage`, `Mail`, `Http`, business logic | `tests/Architecture/ObserverArchitectureTest.php` |
| **Publishing**<br>`app/Publishing` | The readiness and publishing rules (`ContentReadiness`) with their SQL twins (`…Criteria`, `ContentReadinessSummaryQuery`), named by the `ReadinessCheck` enum | Display text, Filament, Livewire, HTTP; the state change itself (that's `PublishContent` calling `publish()`) | `tests/Architecture/PublishingArchitectureTest.php` |
| **Enum**<br>`app/Enums`, backed by snake_case values (or an existing identifier, as `BundledPostArtwork` uses post slugs) | Cases, `HasLabel`/`HasColor`/`HasIcon`, simple case-to-value mapping | Queries, Builders, Models, Services, HTTP, Tailwind classes, display text as the backed value | `tests/Architecture/EnumArchitectureTest.php` |
| **Data**<br>`app/Data`, `final readonly` | Boundary DTOs, result objects and read models (`ContactMessageData`, `ContentListItem`, `PageMeta`) | Fetching, sending mail, reading the request | No arch test (`.ai/rules/data.md`) |
| **Rule**<br>`app/Rules`, `ValidationRule` | Reused validation, or validation that queries, reads the router or calls a service (`UniqueTagSlug`, `PassesTurnstile`) | Saving, sending, other decisions; living as a closure in a form | `tests/Architecture/RuleArchitectureTest.php` |
| **Exception**<br>`app/Exceptions`, named after the failure | A rule an Action refused (`ContentNotReadyToPublish`), with the details as readonly properties | Generic `LogicException` for business rules | `tests/Architecture/ExceptionArchitectureTest.php` |
| **Contract**<br>`app/Contracts` | Interfaces shared across layers (`PageViewModel`, `Publishable`) | Behaviour | No arch test |

## Plumbing and the Admin

| Layer | Does | Never does | Enforced by |
| --- | --- | --- | --- |
| **Support**<br>`app/Support`, subfolders by technical concern (`Feeds`, `Monitoring`, `Seo`) | Portable building blocks: serialisers, formatters, technical value objects (`PaginatedPageSeo`, `JsonLd`), typed config accessors (`DisplayTimezone`), package callbacks | Models, Presenters, ViewModels, `App\Http`, Filament, the Request, `abort*`, business rules, I/O as its purpose | `tests/Architecture/SupportArchitectureTest.php` |
| **Filament**<br>`app/Filament`, one folder per resource | Layout, labels, visibility, `->authorize()`, notifications, calling Actions (`PublishContentAction` → `PublishContent`) and Rules, mapping Query results into stats, rows and links | Writes beyond one model call (use an Action), repeated or non-trivial queries (use a Query), domain rules | `tests/Architecture/FilamentArchitectureTest.php` (`.ai/rules/filament.md`) |
| **Provider**<br>`app/Providers` | Container bindings, rate limiters, framework and package configuration | Page or business logic | `tests/Architecture/ProviderArchitectureTest.php` (`.ai/rules/providers.md`) |

## Where Does a New Class Go?

Answer in order and stop at the first yes.

1. Does it change state (save, delete, send, publish) as one unit? **Action.** If
   it must run later or retry, the Action dispatches a **Job**.
2. Does it run one long operation over many records for a command? **Workflow**
   service.
3. Does it talk to an outside system or check the runtime? **Integration
   service.**
4. Does it only read the database? **Query.** If it changes a builder someone
   else passes in, it's **Criteria**.
5. Is it a business rule a product owner would recognise? A model method for
   one record's state, `app/Publishing` for readiness, a **Rule** if it judges
   input, and a named **Exception** when an Action refuses.
6. Does it shape a whole page's data or SEO? **ViewModel.** Formatting or a URL
   for one model? **Presenter.**
7. Does it turn given data into a string or bytes? **Renderer** (or a
   Generator in `app/Services` if it needs a model).
8. Does it carry data across a boundary? **Data** DTO. A fixed set of values?
   **Enum.**
9. Is it portable plumbing that knows nothing about this site? Run the Support
   checklist.

### The Support Checklist

Any yes to 1 to 4 means the class goes elsewhere.

1. Does it import anything from `App\` outside `App\Support`, or Filament or
   Livewire? Use a Presenter, ViewModel, Query, Action or domain class.
2. Does it name a route, model, content slug or site-specific string? Use a
   Presenter, enum or config.
3. Is I/O or a state change its purpose, or does it call `abort()` or
   `redirect()` or read the Request? Use a Service, Action, Query, controller or
   FormRequest.
4. Would a product owner call what it does a business rule? Use an Action with a
   named exception, or a domain folder such as `app/Publishing`.
5. Can it be named after a technical concern and copied to another Laravel app
   unchanged (apart from config keys)? Only then is it Support.

Creating a new base folder under `app/` needs the owner's approval first.

## Known Gaps

None on `develop` as of 2026-10-08. When code that doesn't follow the tables
has to merge, list it here with the fix it needs, and delete the line when the
fix merges.

The admin `Gate::before` returning `false` for non-administrators is a decision,
not a gap: Filament allows any ability on a model without a policy unless a
before-callback denies it (`.ai/rules/providers.md`).

## Related

- [Architecture overview](../architecture.md) and the feature pages beside this
  one.
- [Testing](../testing.md) for which suite a test belongs in.
- `.ai/rules/*.md` for the full rule text of each layer.
