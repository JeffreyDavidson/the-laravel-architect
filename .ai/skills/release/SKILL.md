---
name: release
description: "Runs a calendar release of this site end to end: cut release/YYYY.MM.N from develop, open the release PR, merge it with a merge commit, wait for staging on main, dispatch Promote production, tag the deployed commit, and fast-forward develop. Use when the user asks to prepare, cut, merge, promote, or tag a release, or to check release or staging status. Policy lives in docs/releases.md; this skill is the executable sequence and its gotchas."
metadata:
  author: thelaravelarchitect
---

# Release

Read `docs/releases.md` first, then `docs/operations.md` before any production step. The user decides each stage; run only the stages they asked for.

- "Prepare the release": steps 1-3.
- "Merge it": steps 4-5. "Promote it": steps 6-7. Then 8-9.
- Never merge, promote, or tag on your own initiative. The approval of the `production` environment in GitHub belongs to the user; you cannot give it.

## 1. Preconditions

```bash
git fetch -q --tags origin && git switch develop && git pull --ff-only origin develop
git log --oneline origin/main..origin/develop        # what ships
git diff --name-only origin/main..origin/develop -- database/migrations   # migrations?
gh pr list --state open --json number,title,baseRefName
```

`develop` must descend from `main` and contain nothing unreviewed. Pick `N`: the number starts at `0` each month (`2026.10.0`, then `2026.10.1`). Confirm the tag and branch are free: `git tag -l 'vYYYY.MM.*'` and `git ls-remote --heads origin 'release/YYYY.MM.*'`.

## 2. Cut the release

The release branch is exactly the tip of `develop`, with no extra commits. Push it; push CI then staging run automatically.

```bash
git switch -c release/YYYY.MM.N && git push -u origin release/YYYY.MM.N && git switch develop
```

Tell the user to pause merges into `develop` until the fast-forward in step 8. A new commit on `develop` makes that fast-forward impossible.

## 3. Open a draft release PR into main

Title `chore(release): release YYYY.MM.N`. Body (use `--body-file`): the commit list, public-facing changes versus docs/tooling, whether there are migrations or env changes, and that it merges with a regular merge commit. Open it as `--draft`.

## 4. Verify the exact candidate

```bash
gh run list --branch release/YYYY.MM.N --json name,event,status,conclusion,headSha
gh run list --workflow deploy-staging.yml --limit 4 --json databaseId,displayTitle,status,conclusion,createdAt
gh run view <staging-run-id> --json jobs --jq '.jobs[]|.name, ([.steps[]|"  \(.name): \(.conclusion)"]|join("\n"))'
```

Gotchas:

- `Deploy staging` is a `workflow_run`, so its `headSha`/`headBranch` show `main`'s tip, not the candidate. The tested revision is in the job name, `Stage <full sha>`. Match that to the release tip.
- A `skipped` Deploy staging run is the pull_request CI completion; it is expected.
- Require `Deploy and verify the tested revision`, `Run application smoke checks`, and `Recheck the serving revision after smoke tests` all `success`.
- An unauthenticated `curl` of staging gets a Cloudflare Access 302; that is not a failure.

## 5. Merge

Only when the user says to merge. Verify head, base (`main`), and head SHA, then use a merge commit, never squash or rebase:

```bash
gh pr ready N
gh pr merge N --merge --subject "chore(release): release YYYY.MM.N (#N)" --match-head-commit <full sha>
git fetch -q origin main && git log -1 --format='parents: %p | %s' origin/main   # two parents
```

Clean up: `git switch main && git pull --ff-only origin main`, delete the remote release branch after confirming its tip equals the merged head, then `git branch -d release/YYYY.MM.N` (a merge commit makes `-d` work). The user's git hook blocks `git branch -D`; give them the command instead of working around it.

## 6. Wait for main

Push CI on the merge commit, then `Deploy staging` for that exact commit. Find both by commit, not by title:

```bash
gh run list --commit <merge sha> --json databaseId,name,event,status,conclusion
```

Wait in the background (a bounded `until` loop on that command, 20 s sleeps); do not poll in the foreground. Verify the staging run's job is `Stage <merge sha>` and succeeded, and that `origin/main` is still that sha.

## 7. Promote production

Only when the user asked to promote. The runbook wants a verified backup before any production change; a release with no migrations skips the deploy script's automatic backup gate, so remind the user to confirm a recent verified B2 backup or run `php artisan backup:run` then `php artisan app:verify-backup` on the server (you cannot).

```bash
gh workflow run promote-production.yml --ref main -f revision=<full 40-char sha> -f staging_run_id=<staging run id>
gh run list --workflow promote-production.yml --limit 1 --json databaseId,status,url
```

It waits on the `production` environment. Give the user the run URL and tell them to approve it. After they say they approved, watch the run to completion and require deploy, production smoke, and the final revision recheck to succeed. Then verify:

```bash
curl -s https://thelaravelarchitect.com/deployment.json     # revision must equal the sha, note deployment_id
curl -s -o /dev/null -w '%{http_code}\n' https://thelaravelarchitect.com/up   # 200
```

Spot-check the user-visible change on the live site (add `?cb=$(date +%s)` to bypass any cache when reading HTML).

## 8. Tag and synchronize develop

Tag the deployed full sha with the same `N`; annotated, never force, never reuse:

```bash
git tag -a vYYYY.MM.N <full sha> -m "Release YYYY.MM.N

Production deployment of <sha>
Forge deployment: <deployment_id>
Promote production run: <url>
Staging run: <url>"
git push origin refs/tags/vYYYY.MM.N
git fetch -q origin main develop && git merge-base --is-ancestor origin/develop origin/main
git switch develop && git merge --ff-only origin/main && git push origin develop
```

If the ancestry check or fast-forward fails, stop and report; never create a sync merge commit or force-push. Tell the user merges into `develop` can resume.

## 9. Report

State what is live (sha, Forge deployment), the tag, `develop`/`main` positions, and anything skipped. If a scheduled `Staging smoke` run failed, read its log (`gh run view <id> --log`) for the real error before calling it a problem; a 15-second cURL timeout with no application error is a transient network fault.
