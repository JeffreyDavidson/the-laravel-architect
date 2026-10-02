---
paths:
  - 'database/migrations/**'
---

# Migrations

## Do not add foreign keys to existing tables with the schema builder on SQLite
On SQLite, `foreignId()->constrained()` on an existing table rebuilds the whole table and silently drops its CHECK constraints (it removed the posts.status check; PostPublishStatusTest caught it). Prefer a new table (`Schema::create`, such as a pivot), or a plain `DB::statement('ALTER TABLE x ADD COLUMN y INTEGER NULL REFERENCES z (id) ON DELETE SET NULL')` guarded by `Schema::hasColumn`, with a comment saying why. Before opening the PR, compare `sqlite_master` for the table before and after on a scratch database. A release that carries a migration takes a verified backup first (the production deploy script does this).
