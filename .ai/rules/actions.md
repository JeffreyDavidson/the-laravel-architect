---
paths:
  - 'app/Actions/**'
---

# Actions

## Invoke actions through handle
Actions expose a public, non-static handle() method and callers invoke them explicitly with ->handle(...). Do not make actions invokable.

## Use an action only for a state change
An action performs one state change, owns its guards and transaction, and is named with an imperative verb. Code that only reads and serialises is not an action: put the reads in a Query, the shaping in a ViewModel and the output in a Renderer in app/Support/Feeds (the RSS feeds, sitemap and robots.txt follow this split).
