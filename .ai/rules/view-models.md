---
paths:
  - 'app/ViewModels/**'
---

# View Models

## Let page ViewModels own payloads and SEO
Page ViewModels assemble the view payload and own the page's SEO and JSON-LD. Controllers inject them and translate the HTTP request and response.

## Return the page's PageMeta from every page ViewModel
Every page ViewModel implements `App\Contracts\PageViewModel` and returns an `App\Data\PageMeta` (the page's `SEOData` plus its own JSON-LD nodes) under the `pageMeta` key from `data()` and `previewData()`, documented as `pageMeta: PageMeta` in the array shape. The site layout reads SEO and JSON-LD from that value only; never add SEO under other view-data keys or let a model build it. A preview returns `noindex, nofollow` SEO and no page nodes. A content page passes its own `SEOData` through `Concerns\AppliesStoredSeo` so the SEO fields saved in the admin fill what the page leaves null. Put the site-wide WebSite entity, the author reference, fixed-page nodes and Home-first breadcrumbs in `SiteStructuredData`, one model's schema node on its presenter, and generic shapes in `App\Support\Seo\JsonLd`. Only the feed ViewModels (RSS, newsletter RSS, sitemap, robots.txt) are not page ViewModels; `tests/Architecture/ViewModelArchitectureTest.php` lists them.

## Call Queries and own the page's HTTP decisions
A page ViewModel injects the Queries it needs and takes validated filters and bound models as arguments. It owns what the Query leaves out: paginator links (withPath, appends, withQueryString, fragment) and the 404s that belong to the page, such as an unknown filter value. For a paginated listing, call PaginatedPageSeo::forCurrentPage() and then abort_if($page->isOutOfRange(), 404); PaginatedPageSeo only reports the range and never aborts.
