---
paths:
  - 'app/Publishing/**'
---

# Publishing

## Keep the publishing rules and their SQL form together in app/Publishing
app/Publishing holds the content readiness and publishing business rules and their SQL twins: `ContentReadiness` (the per-record checklist and the required-to-publish checks), `ContentReadinessCriteria` and `ProjectReadinessCriteria` (builder modifiers that apply the same checks in SQL; a class that changes a builder passed to it is named `…Criteria`), and `ContentReadinessSummaryQuery` (counts built on the criteria). Name checks with the `App\Enums\ReadinessCheck` enum, never string keys. When a check changes, change it in both `ContentReadiness` and `ContentReadinessCriteria`; `tests/Integration/Publishing/ContentReadinessCriteriaTest.php` proves they agree.

Keep display text out: rules return `ReadinessCheck` cases and verdicts, and the caller formats them (`ReadinessCheck::getLabel()`, `ReadinessColumn`). The state change itself stays on the model (`publish()`), and creator-kit's `PublishContent` action is the only domain code that calls it, throwing the package's `ContentNotReadyToPublish` when a required check fails. It asks `App\Publishing\ContentPublishingReadiness` (bound to the package's `PublishingReadiness` in `AppServiceProvider`) for the missing checks' labels, so the models never call app/Publishing themselves. `tests/Architecture/PublishingArchitectureTest.php` forbids `App\Models` from using `App\Publishing` and app/Publishing from using Filament, Livewire or HTTP classes.
