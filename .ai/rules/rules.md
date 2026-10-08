---
paths:
  - 'app/Rules/**'
---

# Rules

## Put non-trivial validation rules in app/Rules
A validation rule that is reused, queries the database, reads the router or calls a service lives in app/Rules as a final class implementing `Illuminate\Contracts\Validation\ValidationRule`. Do not write it as a closure inside a Filament form, FormRequest or controller; reference the rule class from there instead. A rule answers only "is this input acceptable": it does not save, send, or make other business decisions. Set `public bool $implicit = true` only when a missing or empty value must still be checked. `tests/Architecture/RuleArchitectureTest.php` enforces final and the interface.
