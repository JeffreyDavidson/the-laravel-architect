---
paths:
  - 'app/Data/**'
---

# Data

## Use readonly boundary DTOs
Use native final readonly classes for data crossing application boundaries. DTOs map and carry data; they do not fetch data, send mail, or depend on HTTP requests.
