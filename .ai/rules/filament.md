---
paths:
  - 'app/Filament/**'
---

# Filament

## Keep Filament resources self-contained
Organize each Filament resource under its resource namespace, keeping its resource class, pages, schemas, and tables together. Keep reusable Filament components outside individual resource folders.

## Read through Query classes and write through Actions
Widgets, pages, navigation badges and modals call Query classes for anything beyond the current record and only map the results to stats, rows, labels and URLs. A count shown in more than one place has one method on `App\Queries\AdminMetricsQuery`, so a badge and a dashboard stat cannot disagree (the Posts badge is drawn by creator-kit's `PostResource` and counts by the same statuses as `postsInReview()` and `draftPosts()`). Writes beyond a single model call, and every queued job, go through an Action (`RetryContactInquiryEmails`, `SendNewsletterIssue`); catch its domain exception and show it as a notification. Caching, when a read is expensive enough to need it, lives inside the Query (`ContentReadinessSummaryQuery::outstandingCounts()`), never in a widget or resource; badge counts are cheap enough to stay uncached, so FilaCheck's `navigation-badge-not-cached` rule is disabled in `config/filacheck-pro.php`. `tests/Architecture/FilamentArchitectureTest.php` forbids the `DB` and `Cache` facades and `App\Jobs` in app/Filament and query builders and models in widgets; `tests/Architecture/JobArchitectureTest.php` limits jobs to Actions, Jobs, Providers and Console.
