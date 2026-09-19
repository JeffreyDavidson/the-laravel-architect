---
paths:
  - 'app/Services/**'
---

# Services

## Let workflows own complete operations
A workflow service should own the full application operation it names, including external calls, persistence coordination, and aggregation across supported resources. Commands should not split that operation across multiple injected collaborators.
