---
paths:
  - '**'
---

# General

## Prune merged branches after PR merge
After verifying a pull request has merged, prune stale origin remote-tracking refs and remove the merged local and remote head branches when they still exist. Never remove main, develop, branches with post-merge commits, or branches checked out in another worktree; verify the PR head tip before cleanup.
