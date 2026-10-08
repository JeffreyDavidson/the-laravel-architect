---
paths:
  - 'app/Observers/**'
---

# Observers

## Keep lifecycle side effects in observers
Keep model lifecycle side effects involving storage, cache invalidation, or external resources in observers or dedicated lifecycle services. Use after-commit behavior when the side effect depends on committed database state.

## Delegate stored media to the lifecycle service
Observers never touch the filesystem themselves. For every stored media attribute, call `StoredMediaLifecycle` from `created`, `updated` and `forceDeleted`, passing the variant label only when the attribute has responsive variants. The service deletes replaced or force-deleted originals with their variants and generates new variants after the commit; soft deletes and restores keep files. `tests/Architecture/ObserverArchitectureTest.php` keeps `Storage`, `Mail` and `Http` out of observers.
