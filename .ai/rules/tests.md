---
paths:
  - 'tests/**'
---

# Tests

## Keep test structure and doubles consistent
Keep structural rules in tests/Architecture and HTTP controller behavior in tests/Feature/Http/Controllers. Use jasonmccreary/double for test doubles and prefer Pest expectation chaining.

## Use project test doubles
Use jasonmccreary/double for test doubles; do not introduce direct Mockery mocks or Laravel facade spies. Swap a Double-backed contract into the container or facade instead.
