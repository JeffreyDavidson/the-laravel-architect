---
paths:
  - 'app/**'
---

# App

## Read the layers guide before creating a class
Before adding a class anywhere in app/, read `docs/architecture/layers.md`: it lists every layer (Action, Query, ViewModel, Presenter, Workflow, Support and the rest) with what it does, what it never does, its folder and naming, and the architecture test that enforces it, plus the checklist for deciding where new code goes. Then read the `.ai/rules` file for that folder. Folders without their own rule file (Http/Requests, Http/Middleware, Jobs, Livewire, Contracts, View) follow the guide's table.

## Read config through the typed accessors
Plain `config('x.y')` returns mixed, which PHPStan rejects when the value goes into a typed parameter such as `escape(string $value)` or a foreach. Use `config()->string()`, `->integer()`, `->boolean()` or `->array()` (existing code uses `config()->integer('content.post_review_interval_days')`). Add an `@var list<positive-int>` style docblock when a narrower element type is needed.

## Declare strict types and make classes final
Start every file in app/ with `declare(strict_types=1);` and declare every class `final`. Leave a class open only when another class or a test double extends it; those exceptions are listed in `tests/Architecture/ConventionsArchitectureTest.php`, so update it with the reason when you add one. Mark a final class `readonly` only when it is stateless: every property is promoted or readonly, nothing mutates or memoises state, and it does not extend a framework class (models, Filament, Livewire, mailables). Do not repeat `readonly` on the properties of a `readonly` class. `composer rector` applies strict types and readonly, and the arch test enforces strict types and final.
