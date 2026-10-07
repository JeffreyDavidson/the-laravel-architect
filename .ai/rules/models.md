---
paths:
  - 'app/Models/**'
---

# Models

## Keep HTTP concerns out of models
Keep Eloquent models focused on persistence and domain behavior. Do not generate routes, signed URLs, redirects, or other HTTP/presentation output in models; place that behavior in controllers, view models, actions, or support services.

## Keep file changes out of models
Models do not delete, write or generate stored files in boot hooks or concerns, and they do not use `App\Services` (`tests/Architecture/ModelArchitectureTest.php`). Register an observer with `#[ObservedBy]` and let it delegate to `StoredMediaLifecycle`. Cascades between models (a podcast trashing, restoring or force deleting its episodes) also live in the owning model's observer, using `deleting`, `trashed` and `restoring` rather than overriding framework methods such as `performDeleteOnModel`.
