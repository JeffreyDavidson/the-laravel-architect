---
paths:
  - 'app/Queries/**'
---

# Queries

## Keep reusable read composition in Query objects
Use app/Queries for reusable or non-trivial read composition. Controllers and ViewModels may perform simple page-local reads, but shared filtering, ordering, pagination, and relationship loading belong in Query objects.

## Name Query classes after their read responsibility
Name Query classes after the data they return or the read use case they own, followed by Query. Prefer descriptive names such as ArchiveListingQuery or BlogListingQuery over route-action names such as IndexQuery. Keep query modifiers named for the concern they apply, and do not use Query objects for writes or unrelated orchestration.
