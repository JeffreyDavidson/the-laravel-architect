---
paths:
  - 'app/Models/**'
---

# Models

## Keep HTTP concerns out of models
Keep Eloquent models focused on persistence and domain behavior. Do not generate routes, signed URLs, redirects, or other HTTP/presentation output in models; place that behavior in controllers, view models, actions, or support services.
