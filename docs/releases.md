# Release process

The project uses simplified gitflow and calendar versions. Feature, fix,
refactor, documentation, and maintenance pull requests are squash-merged into
`develop`. A release promotes the already-reviewed `develop` commit to `main`
without changing it.

## Version and branch names

Release versions use `vYYYY.MM.N`, where `N` starts at `0` each month and
increments for every production release in that month. The release branch uses
the complete version:

```text
release/v2026.09.0
```

Do not reuse a version or move an existing release tag.

## Prepare a release

1. Confirm every intended change has been merged into `develop` and its
   required `Laravel` check passed.
2. Confirm the production backup and rollback prerequisites in
   [`operations.md`](operations.md) are available.
3. Update local `develop` without rewriting history:

   ```bash
   git fetch --prune origin
   git switch develop
   git pull --ff-only origin develop
   ```

4. Create `release/vYYYY.MM.N` from that exact `develop` commit and push it.
5. Open a pull request from the release branch into `main` titled
   `release: vYYYY.MM.N`. Its description should identify the commit being
   promoted, summarize user-visible and operational changes, call out
   migrations or one-time commands, and state the rollback plan.
6. Verify the pull request base is `main`, its head is the expected release
   branch, the diff contains only the intended unreleased changes, and all
   required checks pass.

A release branch is a promotion boundary, not a second development branch. Do
not make release-only code changes on it. If verification finds a defect, fix it
through a focused pull request into `develop`, then recreate or advance the
unmerged release branch from the newly verified `develop` commit.

Dependency updates are performed intentionally in their own pull requests.
Preparing a release does not update Composer or npm dependencies implicitly.

## Merge and tag

Merge the release pull request with a regular merge commit so the release
boundary remains visible:

```bash
gh pr merge PR_NUMBER --merge --delete-branch
```

Never squash or rebase a release pull request. After the merge, update local
`main`, verify its merge commit is the commit accepted by the pull request, and
create an annotated tag on that commit:

```bash
git fetch --prune origin
git switch main
git pull --ff-only origin main
git tag -a vYYYY.MM.N -m "Release vYYYY.MM.N"
git push origin vYYYY.MM.N
```

Confirm the tag resolves to the release merge commit before deployment. Tags
are permanent production identifiers; never force-push or replace one.

## Deploy and verify

Merging into `main` supplies the commit to the configured Forge deployment.
Confirm the exact Forge organization, `cold-moon` server, site, and release
commit before manually triggering or changing any production operation.

Follow the ordered deployment and post-deployment checks in
[`operations.md`](operations.md). At minimum:

1. Validate the rollback artifacts before migrations.
2. Run the production configuration verifier before applying migrations.
3. Confirm the deployed commit equals the tagged release commit.
4. Run the deployment verifier with that immutable commit SHA.
5. Dispatch the `Production smoke` workflow and require it to pass.
6. Record the version, commit, deployment result, and any one-time commands
   without recording secrets or private data.

## Hotfixes

Use `hotfix/<short-description>` from `main` only for an urgent production
correction that cannot wait for the next release. Open the hotfix pull request
into `main`, require CI, and squash-merge it. Then immediately reproduce or
cherry-pick that single squash commit onto a focused branch from `develop` and
open a pull request into `develop`, preventing the fix from disappearing from a
later release.

Tag the corrected production commit with the next `vYYYY.MM.N` version and run
the same deployment and verification checklist. Do not move the previous tag.
