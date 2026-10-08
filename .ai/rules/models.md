---
paths:
  - 'app/Models/**'
---

# Models

## Keep HTTP concerns out of models
Keep Eloquent models focused on persistence and domain behavior. Do not generate routes, signed URLs, redirects, or other HTTP/presentation output in models; place that behavior in controllers, view models, actions, or support services.

## Expose paths and IDs, not URLs
Models store and expose file paths and external IDs (`featured_image_path`, `cover_image_path`, `youtube_id`, `Episode::transistorEpisodeId()`) and never build public, storage, signed or embed URLs: those belong to the model's presenter in `app/Presenters`. Do not add URL accessors or use the `Storage` facade (`tests/Architecture/ModelArchitectureTest.php`).

## Keep the stored SEO row, not page SEO
Content models keep laravel-seo's `HasSEO` trait: its `seo` relation stores the SEO fields an editor saves in the admin, and `DeletesOwnedContent` removes the row with the content. Do not implement `getDynamicSEOData()` or build `SEOData`, and do not use presenters, `route()` or `url()` in a model: the page ViewModel builds the page's SEO and applies the saved row through `AppliesStoredSeo`. `tests/Architecture/ModelArchitectureTest.php` forbids `App\Presenters`, `route`, `url` and `SEOData` in `App\Models`.

## Keep file changes out of models
Models do not delete, write or generate stored files in boot hooks or concerns, and they do not use `App\Services` (`tests/Architecture/ModelArchitectureTest.php`). Register an observer with `#[ObservedBy]` and let it delegate to `StoredMediaLifecycle`. Cascades between models (a podcast trashing, restoring or force deleting its episodes) also live in the owning model's observer, using `deleting`, `trashed` and `restoring` rather than overriding framework methods such as `performDeleteOnModel`.
