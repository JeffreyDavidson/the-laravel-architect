# Admin panel

The Filament panel is available at `/admin`. Its styling is a separate Vite
entry (see [Frontend](frontend.md#assets-and-vite)).

## Access and authorization

Panel admission requires the native `is_admin` flag, and app-based multi-factor
authentication is required in production.

Authorization uses one app-wide `Gate::before` callback in `AppServiceProvider`
instead of per-model policies: administrators may perform every ability and
everyone else is denied. A global before-callback runs for every ability whether
or not a policy method exists, and Filament honors it, so every panel resource
is covered, including new ones.

The callback returns `false` for non-administrators, not `null`. With no
policies, Filament allows a resource ability (`canViewAny()`, `canEdit()` and
the rest) unless a before-callback denies it, so `null` would leave the panel's
`canAccessPanel()` check as the only barrier. `AppServiceProviderTest` fails if
any resource ability opens up for a non-administrator.

Because the gate cannot restrict an administrator, capability limits live on the
resources:

- subscribers and contact inquiries cannot be created (`canCreate()` returns
  false);
- subscribers have no edit page.

Future model-specific rules belong in resources, actions, or dedicated classes
rather than policies.

## Panel configuration

`AdminPanelProvider` is the only provider that configures Filament (an
architecture test enforces this). Besides the panel itself it sets the panel
timezone to the site's display timezone (`app.display_timezone`).

- **Sidebar.** Each resource and page declares its `$navigationGroup` (an
  `App\Enums\NavigationGroup` case), `$navigationSort` and icon, and that is the
  only navigation definition. Filament orders the groups by enum case order
  (Publish, Library, Audience, Operations) after the ungrouped Dashboard, and
  items within a group by `$navigationSort`. A new resource appears as soon as
  it declares a group.
- **Render hooks.** Hook markup lives in Blade views under
  `resources/views/filament`: the sidebar's "New post" link
  (`sidebar/new-post-link`), the login kicker (`auth/login-kicker`) and the
  login theme switcher (`auth/theme-switcher`).
- **User menu.** "View Site" and a GitHub link to `app.repository_url`.

## Content resources

Publishing actions, slug locking and the trash are described in
[Publishing and content](publishing-and-content.md). Admin "Preview" actions
open signed preview URLs (see
[Public site and SEO](public-site-and-seo.md#other-public-routes)). Social
profiles are managed here too (see
[Public site and SEO](public-site-and-seo.md#social-profiles)).

## Custom pages

Beyond the resources, the panel has three custom pages:

- **Media Health** (Operations group) lists the stored images of projects, posts
  and podcasts with their source optimization and responsive-variant status,
  filters by content type and status, and offers a confirmed **Repair variants**
  action where repair is possible. `MediaHealthQuery` reads and filters the
  images into `MediaHealthRecord` objects, the page formats them as table rows
  (file size, dimensions, badge colours), and the repair runs through the
  `RepairImageVariants` action.
- **Editorial Calendar** (Publish group) shows a month grid of posts and
  episodes by publication date, with a list of items that have no date yet, and
  previous, next and today navigation in the display timezone.
  `EditorialCalendarQuery` returns the posts and episodes in the visible range;
  the page places each on its display-timezone day as a `CalendarEntry` and adds
  its edit link.
- **Insights** (Operations group) hosts the editorial-operations and
  content-performance widgets.

## Dashboard counts

The dashboard widgets, the Contact Inquiries navigation badge, the
newsletter send confirmation and the sent-issue delivery summary read their
counts from `AdminMetricsQuery`, so a badge and a dashboard stat always agree.
The Posts badge is drawn by the package's `PostResource`, which counts posts by
the same statuses as `AdminMetricsQuery::postsInReview()` and `draftPosts()`;
`PostResourcePagesTest` checks the badge.
The counts are not cached: each is one aggregate query on a small or indexed
table. `RecentlyEditedContentQuery` feeds the recent activity list. The content
readiness counts are the exception: `ContentReadinessSummaryQuery` caches them
for a minute because they run the full readiness criteria. Widgets only map
these results to stats, rows and admin URLs.
