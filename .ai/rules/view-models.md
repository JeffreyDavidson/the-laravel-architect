---
paths:
  - 'app/ViewModels/**'
---

# View Models

## Let page ViewModels own payloads and SEO
Page ViewModels assemble the view payload and own SEO metadata, including seoSource. Controllers should inject them and translate the HTTP request and response.

## Call Queries and own the page's HTTP decisions
A page ViewModel injects the Queries it needs and takes validated filters and bound models as arguments. It owns what the Query leaves out: paginator links (withPath, appends, withQueryString, fragment) and the 404s that belong to the page, such as an unknown filter value. For a paginated listing, call PaginatedPageSeo::forCurrentPage() and then abort_if($page->isOutOfRange(), 404); PaginatedPageSeo only reports the range and never aborts.
