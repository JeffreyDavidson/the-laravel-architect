---
paths:
  - 'app/Queries/**'
---

# Queries

## Keep reusable read composition in Query objects
Use app/Queries for reusable or non-trivial read composition. ViewModels inject and call Queries; controllers and Livewire components never do. ViewModels may perform simple page-local reads, but shared filtering, ordering, pagination, and relationship loading belong in Query objects.

## Name Query classes after their read responsibility
Name Query classes after the data they return or the read use case they own, followed by Query. Prefer descriptive names such as ArchiveListingQuery or BlogListingQuery over route-action names such as IndexQuery. Keep query modifiers named for the concern they apply, and do not use Query objects for writes or unrelated orchestration.

## Keep queries free of HTTP, URLs and display formatting
Queries only read. Return models, collections, paginators or readonly DTOs from app/Data with enum properties, and apply every filter inside the query rather than in the caller. Do not abort (a missing record is a null for the caller to answer, not firstOrFail), read the request, build URLs or set paginator links (route, url, withPath, appends, withQueryString, fragment), use App\Http or App\Filament, or format values for display; the ViewModel adds URLs and links and the page or presenter formats. A query may still choose a paginator's page parameter name. tests/Architecture/QueryArchitectureTest.php enforces the HTTP, URL, Filament and abort part. EditorialCalendarQuery shows the split: it returns the posts and episodes, and the EditorialCalendar page adds display days and admin edit links.
