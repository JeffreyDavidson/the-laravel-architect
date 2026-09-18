---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Keep controllers thin and resourceful
Controllers are standalone and expose only invokable or resourceful actions. Do not add private helper methods; move supporting behavior to view models, actions, queries, builders, or services.

## Use standalone singular controllers
Name resource controllers with the singular resource plus Controller, and keep application controllers as standalone classes without an empty shared base controller.
