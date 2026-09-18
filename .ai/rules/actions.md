---
paths:
  - 'app/Actions/**'
---

# Actions

## Invoke actions through handle
Actions expose a public, non-static handle() method and callers invoke them explicitly with ->handle(...). Do not make actions invokable.
