# Release process

`develop` is the integration branch for routine work. Start feature branches
from an up-to-date `develop` and squash-merge reviewed feature PRs into it after
CI passes. `main` contains approved releases; it identifies releasable code, not
necessarily the revision currently deployed to production.

```text
feature branch → reviewed PR → develop
                                  ↓ (pause develop merges)
                         release/YYYY.MM.N → CI → staging → review
                                                       ↓ approved regular merge
main → CI → staging verification → explicit production approval
  ↓ (after successful main staging)
fast-forward develop to main
```

## Cut and validate a release

1. Choose the next calendar release number and cut `release/YYYY.MM.N` from
   `develop` (for example, `release/2026.09.0`). Do not use release branches for
   routine feature, fix, performance, documentation, or dependency work.
2. Pause merges into `develop` while the release is under validation. Keep the
   branch frozen until its release PR is merged or the release is cancelled.
3. Push the release branch. CI runs on that exact branch and, after successful
   push CI, `Deploy staging` deploys the tested full SHA. The staging workflow
   rejects a stale candidate if its source branch has advanced. Review public
   pages, affected behavior, and the authenticated admin boundary on staging.
4. Open a release PR from the release branch into `main`. After review and
   approval, merge it with a regular merge commit to preserve ancestry. Do not
   squash or rebase the release PR.
5. The merge commit on `main` receives push CI. After CI succeeds, staging
   deploys and verifies that exact `main` revision. Production promotion must
   use this successful `main` staging run; the earlier release-branch staging
   run is only for pre-merge validation.

## Validate staging

`Deploy staging` runs after successful **push CI** on `release/*` and `main`,
not PR CI. It checks out the tested full commit SHA and confirms the source
branch still points to it before deployment. Deployment and production
promotion share a concurrency lock, so staging cannot be replaced while
promotion is checking it. Running deployments are never auto-cancelled.

Forge checks out the requested revision before installing dependencies. Only
after activation and the deployment verifier succeed does it publish
`/deployment.json`, containing the full revision and Forge deployment ID. This
file must not be cached by Cloudflare or Nginx. The workflow requires this marker
and `/up`, runs the HTTP smoke suite against the deployed revision, then checks
the marker again. A redirect to Cloudflare login is a failure, not a successful
application response.

Review the public pages, affected behavior and authenticated admin boundary on
the staged revision. For production approval, choose the successful staging run
created for the merged `main` SHA. A newer staging deployment invalidates a
pending manual review; select and review the new revision before promotion.

## Promote production

1. Choose a successful `Deploy staging` run for the full SHA on `main` that was
   created after the release PR merged.
2. Confirm rollback artifacts, migration safety, media requirements and any
   one-time commands from `operations.md`. Code rollback does not undo database
   migrations. Prefer forward-compatible migrations and forward fixes.
3. Dispatch `Promote production` **from main**, providing that staging run ID
   and the full revision. This request is not a substitute for environment
   approval.
4. Review the waiting `production` environment job and approve the exact
   revision. The workflow validates the staging run's workflow, source
   repository, event, branch, conclusion and SHA, and verifies staging still
   serves it before triggering production. Old approvals cannot authorize a
   different SHA.
5. Require production deployment verification and the HTTP smoke suite to pass.
   A timed-out trigger is an uncertain deployment: inspect Forge before retrying.
6. Record the successful production deployment with the next annotated calendar
   tag, `vYYYY.MM.N` (`N` starts at zero each month), on the full deployed SHA.
   Verify no existing tag uses that version, push the tag without force, and
   record the Forge deployment and workflow run. Never move or reuse a tag.

## Synchronize develop after release

After the release has merged into `main` and its resulting revision has passed
CI and staging verification, fetch the latest refs and verify `develop` is an
ancestor of `origin/main`:

```sh
git fetch origin main develop
git merge-base --is-ancestor origin/develop origin/main
git switch develop
git merge --ff-only origin/main
git push origin develop
```

The push is a normal fast-forward update. Do not create a sync merge commit or
force-push. If the ancestry check or fast-forward fails, stop and resolve the
branch history before updating `develop`.

Dependency updates remain separate, intentional PRs; deployments install
lockfiles without updating them. NewDebugBar is development-only and currently
has no stable Packagist release. Keep its exact resolved commit in
`composer.lock`; review that commit intentionally before updating it rather than
running an unattended dependency refresh.

## Hotfixes and rollback

If `main` matches the deployed revision, a focused fix follows the normal
feature-to-`develop`, release, staging and approval path. If `main` contains
unreleased work, start `hotfix/<description>` from the verified production
tag/SHA, not the moving tip of `main`. Agree an exceptional staging/promotion
procedure first: the normal pipeline accepts release and main candidates.
Review and test that isolated fix, obtain explicit production approval, then
promptly integrate it into `develop` and `main` through the agreed release path.
Never include unrelated unreleased work accidentally.

Rollback is also an explicit operational decision, not automatic branch
rewriting. Verify the previous release remains compatible with the current
database and stored media before reactivation. Restore data only through a
separately approved recovery procedure. Record failed releases and recovery;
do not move tags to conceal them.
