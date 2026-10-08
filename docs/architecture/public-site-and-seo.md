# Public site and SEO

Public pages are server-rendered Blade views. This page covers how they get
their data, how they describe themselves to search engines and social sites,
and the public routes beside the main pages.

## Pages and ViewModels

Page controllers pass their data through ViewModels in `app/ViewModels`, which
assemble page payloads such as the homepage, blog index, category and tag
archives, the newsletter and content archives, search, and the post, podcast,
episode, and project detail pages. Signed previews reuse the same ViewModels
through their `previewData()` methods.

Presenters in `app/Presenters` format one model for display and never query.
Models expose stored paths and IDs only; presenters own every URL built from
them and the media display. Each presenter takes its collaborators
(`ResponsiveImageVariants`, Vite, the URL generator) through its constructor and
is built with `XPresenter::from($model)`. They build the stored-image URLs
(`PostPresenter::featuredImageUrl()`, `ProjectPresenter::featuredImageUrl()`,
`PodcastPresenter::coverImageUrl()`), the YouTube links
(`VideoPresenter::youtubeUrl()` and `embedUrl()`), the Transistor player
(`EpisodePresenter::transistorEmbedUrl()` from `Episode::transistorEpisodeId()`),
the signed newsletter links (`SubscriberPresenter`), and each publishable
item's `publicUrl()`, `previewUrl()` and `publicOrPreviewUrl()` (Post, Project,
Episode and NewsletterIssue presenters, sharing `LinksToPublicPageOrPreview`).
They own the image fallbacks: `PostPresenter::artwork()` and
`shareImageUrl()` (uploaded image, the bundled launch artwork named by the
`BundledPostArtwork` enum, then the generated OG card for sharing), `PodcastPresenter::cover()` and `displayColor()`
(uploaded cover, then the bundled artwork in `config/podcasts.php`), and
`ProjectPresenter::featuredImage()`. The image methods return an
`App\Data\ResponsiveImage` (`src` plus an optional WebP `srcset`) that the
`post-artwork`, `podcast-cover` and `projects.artwork` components render.
`PodcastPresenter::platformLinks()` lists the show's linked listening platforms
for the subscribe buttons. `EpisodePresenter` formats episode codes, durations
and YouTube video IDs, and `EpisodeShowViewModel` turns them into the episode
page's display flags.

A public page request runs in one direction. The route binds slugs and applies
middleware, a FormRequest normalises and validates any filters (bad public
filters return 404), and the controller passes only that validated input and the
bound models to the page's ViewModel. The ViewModel calls the Queries it needs,
sets paginator links, returns 404 for an out-of-range page or an unknown filter
value, builds the SEO metadata, and returns the view data. Queries only read and
return models, paginators or DTOs: they never abort, read the request or build
URLs. Architecture tests keep controllers and Livewire components from calling
Queries and keep HTTP and URLs out of Queries.

Reusable content selection, including the blog index, archive and search
listings, related posts, related projects, and adjacent-episode navigation,
lives in query objects that ViewModels call.
The newsletter subscription lifecycle and contact message delivery live in
focused actions, because each changes state.

The RSS feeds, the sitemap and robots.txt change nothing, so they are not
actions. Each splits into three read-only parts: a Query reads the content
(`RssFeedQuery`, `NewsletterRssFeedQuery`, `SitemapQuery`), a ViewModel turns it
into plain arrays with URLs and dates (`RssFeedViewModel`,
`NewsletterRssFeedViewModel`, `SitemapViewModel`, `RobotsTxtViewModel`), and a
Renderer in `app/Support/Feeds` serialises those arrays without knowing about
models (`RssChannelRenderer`, `SitemapRenderer`, `RobotsTxtRenderer`). The
controller passes the ViewModel's data to the Renderer and sets the response
headers.

Route-model binding uses content slugs, while publication scopes keep drafts and
future content off public pages, feeds, and the sitemap (see
[Publishing and content](publishing-and-content.md)).

## SEO metadata

Every page ViewModel implements `App\Contracts\PageViewModel`: its `data()` (and
`previewData()` for previewable pages) returns the page's `App\Data\PageMeta`
under the `pageMeta` key. `PageMeta` holds the page's `SEOData` and its own
JSON-LD nodes. ViewModels stay stateless and return typed arrays, so the key is
documented in each array shape, which PHPStan checks, and
`tests/Architecture/ViewModelArchitectureTest.php` checks that every page
ViewModel implements the contract and documents the key. The site layout
(`components/layouts/site`) takes the `PageMeta` as its only SEO input: it
renders the tags with laravel-seo's `seo($pageMeta->seo)` and the JSON-LD
through `SiteStructuredData::graph()`. Previews return a `noindex, nofollow`
`PageMeta` with no page nodes, and the 404 page builds its own `PageMeta` in the
view.

Content models keep laravel-seo's `HasSEO`: its `seo` relation holds the SEO
fields an editor saves in the admin (the Filament SEO section), and deleting
the content removes the row. Models no longer implement `getDynamicSEOData()`.
The post, project, episode and newsletter issue ViewModels build the page's own
`SEOData` (title, description, image from the presenter, dates and type) and
pass it through `Concerns\AppliesStoredSeo`, which fills each field the page
leaves null from the saved row, the same precedence laravel-seo's
`SEO::prepareForUsage()` gives a model's dynamic SEO. The page's values win, so
the saved description, image, robots and canonical URL apply when the page has
none, while the title always comes from the content.

- Blog posts share as articles with publication times and one wide image (the
  uploaded featured image, the bundled launch artwork, or the generated
  `og-image` card), used in both the social tags and the Article JSON-LD.
- Projects share their featured image and podcasts their cover (uploaded or
  bundled), falling back to the site logo when there is none.
- Social tags use the `en_US` Open Graph locale from `config/seo.php` while the
  app locale stays `en`.
- The 404 page is `noindex, nofollow` and renders no canonical link.
- A category-filtered `/blog` page canonicalizes to its `/blog/category/{slug}`
  archive.
- Newsletter action pages are explicitly excluded from indexing.

## Structured data

Each page's JSON-LD graph is the site-wide WebSite entity (with its author, the
Person on the About page), followed by the nodes the page ViewModel put in its
`PageMeta`. Every page, including the 404 page, gets the WebSite entity.

- `App\ViewModels\SiteStructuredData` owns the site-wide entity and the
  references pages use: `graph()`, `authorReference()`, `page()` for the fixed
  pages (home, about, contact, privacy, uses) and `breadcrumbs()`, which starts
  every trail at Home. The layout injects it, and the `BlogIndex` component uses
  it for the graph it sends with `blog-metadata-updated`.
- Presenters describe their one model: `PostPresenter::articleSchema()`,
  `ProjectPresenter::creativeWorkSchema()`, `PodcastPresenter::seriesSchema()`
  and `seriesReference()`, and `EpisodePresenter::episodeSchema()`. They take
  the author reference from the ViewModel and build URLs with their injected URL
  generator.
- Page ViewModels decide which nodes their page has and in what order, and
  build the listings: the collection name, its canonical URL and the items, with
  `CollectionListing::paginated()` continuing item positions across pages.
- `app/Support/Seo` keeps only generic shapes that take plain names and URLs:
  `JsonLd::breadcrumbList()`, `JsonLd::collectionPage()` (a CollectionPage and
  its ItemList), `JsonLd::isoDuration()`, `CollectionListing` and
  `PaginatedPageSeo`. They never import models, presenters or the request.

## Archives and pagination

Paginated public archives reject out-of-range pages and use page-specific
titles, descriptions, canonical and collection URLs, and continuous item
positions. `PaginatedPageSeo` builds the page-specific metadata and reports
whether the page is out of range; each listing's ViewModel turns that into the
404. Dynamic sitemap archives report the latest modification date from
their public content.

## Blog archive and search

The blog archive returns 12 articles per page, ordered by publication date and
ID. Its GET search and category filters work without JavaScript and persist in
pagination links.

Search covers article titles, excerpts, and localized tag names; `%` and `_` are
literal search characters. Search result pages are excluded from indexing, and
invalid filters or out-of-range pages return 404. How the blog index updates
without a reload is in [Frontend](frontend.md#livewire-on-the-blog).

## Projects

Public project pages present summaries, optional screenshots, authored
write-ups, and contact links. Project repository URLs remain available in admin
records but are not rendered as public links or included in project JSON-LD.
Editors should also avoid inserting private repository URLs into public
descriptions, write-ups, or website-link fields. The general author GitHub
profile link is independent of project repository visibility.

The projects index separates featured and additional projects using the shared
`projects.index-entry` Blade component, styled entirely with Tailwind utilities.
Entries show existing screenshots with available responsive variants, or use a
text-only layout without fabricated fallback imagery. An empty-state message and
the persistent contact section keep the page useful when no projects are
published. See also [Portfolio presentation](../portfolio-presentation.md).

## Social profiles

Social profile URLs and their enabled, placement, display-label, and ordering
settings are managed in Filament and stored in `social_profiles`. The footer and
contact page use separate visibility flags, while the homepage's YouTube call to
action uses the enabled YouTube profile. The initial migration preserves the
existing public links.

## Other public routes

- `/newsletter` is a paginated archive of published issues (12 per page,
  out-of-range pages return 404), and `/newsletter/rss` is an RSS 2.0 feed of
  the 20 newest published issues (`NewsletterRssFeedQuery`). Individual
  issues live at `/newsletter/{slug}` and unpublished ones return 404. The issue
  form rejects a slug that matches a static `/newsletter/*` route (such as `rss`
  or `confirmed`), because those routes are registered first and would make the
  issue unreachable.
- `/archive` (`ArchiveController`, `ArchiveViewModel`, `ArchiveQuery`) is one reverse-chronological
  list of published posts, projects, active podcasts, newsletter issues,
  episodes and videos, filtered by `type` and `year` and paginated at 18 per
  page with a 404 for out-of-range pages. Videos link out to YouTube.
- Admin "Preview" actions open signed `/preview/*` routes (`preview.post`,
  `preview.project`, `preview.episode`, `preview.newsletter-issue`), generated by
  each content presenter's `previewUrl()` (the `LinksToPublicPageOrPreview`
  concern) as temporary signed URLs valid for two hours. They render
  the unpublished item through the same ViewModels' `previewData()`, titled as a
  preview with `noindex, nofollow`, and a missing or invalid signature is
  rejected.

## Feeds, sitemap and OG images without sessions

The feeds (`/rss`, `/newsletter/rss`), `/sitemap.xml` and `/og-image/{post}` run
without session, cookie and forgery-token middleware, because feed readers and
crawlers poll them and the database session driver would otherwise insert a
`sessions` row on every hit.

The feeds and sitemap remove the whole `web` group like `/robots.txt`. The OG
image route removes only those middleware classes, because excluding the group
would also drop the route-model binding for its post slug.

## robots.txt

`/robots.txt` is served by `RobotsController`, which renders the policy from
`RobotsTxtViewModel` with `RobotsTxtRenderer`. The route
removes the `web` middleware group, so the response sets no session or CSRF
cookies, and it is sent with `Cache-Control: public, max-age=3600` so it behaves
like a static file at the CDN. Do not add a static `public/robots.txt`, because
the web server would serve it before Laravel.

The content depends on `app.deployment_environment` (`TLA_DEPLOYMENT_ENVIRONMENT`),
read through `App\Enums\DeploymentEnvironment::current()` (null for any value
other than `production` or `staging`), rather than `APP_ENV`, because staging
also runs with `APP_ENV=production`.

- Only the `production` deployment allows crawling. It lists `/admin` and
  `/preview/` (the signed preview URLs) as disallowed and advertises the sitemap
  from `APP_URL`. It does not disallow `/search`: its results are `noindex`, and
  crawlers must be able to fetch them to read that tag.
- Every other deployment, including staging, answers `Disallow: /`.

Cloudflare is known to rewrite the response in front of production; see
[robots.txt on production](../operations/environments.md#robotstxt-on-production)
for that open issue and the after-deploy check.
