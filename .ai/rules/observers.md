---
paths:
  - 'app/Observers/**'
---

# Observers

## Keep lifecycle side effects in observers
Keep model lifecycle side effects involving storage, cache invalidation, or external resources in observers or dedicated lifecycle services. Use after-commit behavior when the side effect depends on committed database state.
