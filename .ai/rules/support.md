---
paths:
  - 'app/Support/**'
---

# Support

## Keep app/Support for portable technical building blocks
`app/Support` holds technical building blocks that know nothing about this site's models, routes, content or business rules, like `Illuminate\Support` for the framework: serialisers, formatters, parsers, technical value objects, typed config accessors, and package callbacks. A Support class must work unchanged in another Laravel app apart from config keys. Group subfolders by technical concern (`Feeds`, `Monitoring`, `Seo`), never by domain area.

## Check before adding a Support class
Answer all five; any "yes" to 1-4 means the class goes elsewhere.
1. Does it import anything from `App\` outside `App\Support`, or Filament or Livewire? Use a Presenter, ViewModel, Query, Action or domain class.
2. Does it name a route, model, content slug or site-specific string? Use a Presenter, enum or config.
3. Is IO or a state change its purpose, or does it call `abort()` or `redirect()` or read the Request? Use a Service, Action, Query, controller or FormRequest.
4. Would a product owner call what it does a business rule? Use an Action with a custom exception, or a domain folder such as `app/Publishing`.
5. Can it be named after a technical concern and copied to another app? Only then is it Support.

`tests/Architecture/SupportArchitectureTest.php` forbids `App\Models`, `App\Presenters`, `App\ViewModels`, `App\Http`, `App\Filament`, the Request and `abort*` in `App\Support`, with no exceptions.
