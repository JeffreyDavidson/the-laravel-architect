# Release process

Use short-lived working branches and one permanent integration branch, `main`.
Squash-merge reviewed pull requests after required CI passes. `main` means
releasable code; the production deployment record identifies what is actually
live. Do not create permanent environment branches or routine release branches.

```text
working branch → reviewed PR → main → successful CI → staging
                                                      ↓
                                   smoke checks + operator review
                                                      ↓
                           manual promotion + production approval
                                                      ↓
                                   same revision on production
```

## One-time transition

The existing `develop` branch contains unreleased work. The workflow migration
branch starts from that baseline intentionally. Review its migration changes
separately, then promote the complete reviewed ancestry through a one-time PR
into `main` using a regular merge, with a Conventional Commit subject. This is
the only transition exception to squash merging. Do not squash away the existing
`develop` ancestry, force-push either branch, or deploy production during setup.

Before that merge, disable Forge push-to-deploy for production. Complete the
Forge scripts, GitHub environments, scoped secrets, staging runtime and
Cloudflare setup in `operations.md`. Leave the repository variable
`STAGED_RELEASES_ENABLED` unset until these prerequisites are verified. Deployment
jobs remain disabled when it is unset. Existing CI continues to
check PRs into `develop` during the transition.

Once the transition is verified, direct all new work to `main`. Keep `develop`
read-only until its remaining PRs and worktrees are accounted for; deleting it
is a separate, explicitly approved cleanup. Do not synchronize it after releases.

## Validate staging

The `Deploy staging` workflow runs after successful **push CI on main**, not PR
CI. It checks out the tested full commit SHA and rejects an obsolete candidate
if `main` advanced while CI was running. Deployment and promotion share a
concurrency lock, so staging cannot be replaced while promotion is checking it.
Running deployments are never auto-cancelled.

Forge checks out the requested revision before installing dependencies. Only
after activation and the deployment verifier succeed does it publish
`/deployment.json`, containing the full revision and Forge deployment ID. This
file must not be cached by Cloudflare or Nginx. The workflow requires this marker
and `/up`, runs the HTTP smoke suite against the deployed revision, then checks
the marker again. A redirect to Cloudflare login is a failure, not a successful
application response.

Review the public pages, affected behavior and authenticated admin boundary on
that staging revision. A newer staging deployment invalidates a pending manual
review; select and review the new revision before promotion.

## Promote production

1. Choose a successful `Deploy staging` run for the full revision reviewed.
2. Confirm rollback artifacts, migration safety, media requirements and any
   one-time commands from `operations.md`. Code rollback does not undo database
   migrations. Prefer forward-compatible migrations and forward fixes.
3. Dispatch `Promote production` **from main**, providing the staging run ID and
   the full revision. This request is not a substitute for environment approval.
4. Review the waiting `production` environment job and approve the exact revision.
   The workflow validates the staging run's workflow, source repository, event,
   branch, conclusion and SHA, and verifies staging still serves it before
   triggering production. Old approvals cannot authorize a different SHA.
5. Require production deployment verification and the HTTP smoke suite to pass.
   A timed-out trigger is an uncertain deployment: inspect Forge before retrying.
6. Record the successful production deployment with the next annotated calendar
   tag, `vYYYY.MM.N` (`N` starts at zero each month), on the full deployed SHA.
   Verify no existing tag uses that version, push the tag without force, and
   record the Forge deployment and workflow run. Never move or reuse a tag.

Several safe changes may be integrated before a production release. Do not
merge unfinished behavior merely to batch releases. Dependency updates remain
separate, intentional PRs; deployments install lockfiles without updating them.

## Hotfixes and rollback

If `main` matches the deployed revision, a focused fix follows the normal PR,
staging and approval path. If it contains unreleased work, start
`hotfix/<description>` from the verified production tag/SHA, not the moving tip
of `main`. Agree an exceptional staging/promotion procedure first: the normal
pipeline deliberately accepts only successful main-branch candidates. Review
and test that isolated fix, obtain explicit production approval, then promptly
forward-port it to `main`. Never include unrelated unreleased work accidentally.

Rollback is also an explicit operational decision, not automatic branch
rewriting. Verify the previous release remains compatible with the current
database and stored media before reactivation. Restore data only through a
separately approved recovery procedure. Record failed releases and recovery;
do not move tags to conceal them.
