---
paths:
  - 'app/**'
---

# App

## Read config through the typed accessors
Plain `config('x.y')` returns mixed, which PHPStan rejects when the value goes into a typed parameter such as `escape(string $value)` or a foreach. Use `config()->string()`, `->integer()`, `->boolean()` or `->array()` (existing code uses `config()->integer('content.post_review_interval_days')`). Add an `@var list<positive-int>` style docblock when a narrower element type is needed.
