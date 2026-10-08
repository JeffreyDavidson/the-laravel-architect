---
paths:
  - 'app/Enums/**'
---

# Enums

## Keep enums simple
Enums hold cases, labels, colours, icons and simple case-to-value mapping only. Back them with lowercase snake_case values (`needs_repair`, not `Needs repair`); the one exception is an enum keyed by an existing identifier, such as `BundledPostArtwork`, which is backed by post slugs and return display text from `getLabel()` through Filament's `HasLabel`, with `HasColor` and `HasIcon` for badges. Do not put queries, Eloquent builders, model logic or Tailwind classes in an enum: scopes and status derivation belong on the model (`Subscriber::status()`, `withStatus()`), and CSS belongs in the Blade component that renders it. `tests/Architecture/EnumArchitectureTest.php` forbids `App\Models`, `App\Services`, `App\Queries`, `App\Http` and the Eloquent `Builder` in `App\Enums`.
