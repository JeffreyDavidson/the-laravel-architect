# Forge deploy script

Staging and production are separate Forge sites, and each holds its own copy of
this script. Keep the two copies identical: the site ID guard selects the
per-site behavior. After changing the script, paste it into both sites.
`scripts/forge-deployment.test.mjs` reads the script from this page and checks
its guards and step order, so run `npm run test:deployment` after editing it.

Install this script only after the revision-marker setup in
[Environments](environments.md#revision-marker) is complete.

Forge's `forge_deploy_commit` parameter is metadata, not checkout pinning. The
separate `revision` and `source_branch` hook parameters become
`FORGE_VAR_REVISION` and `FORGE_VAR_SOURCE_BRANCH`; validate both before
executing application code.

- Staging accepts `main` or a numbered `release/YYYY.MM.N` source branch.
- Production accepts only `main`.
- The source branch must still point at the exact tested revision before
  checkout.
- Direct Deploy-button requests without both parameters intentionally fail
  closed.

```bash
set -e

test "$FORGE_SITE_BRANCH" = main
case "$FORGE_SITE_ID" in
    3366565)
        test "$FORGE_SITE_ROOT" = /home/forge/staging.thelaravelarchitect.com
        [[ "${FORGE_VAR_SOURCE_BRANCH:-}" = main || "${FORGE_VAR_SOURCE_BRANCH:-}" =~ ^release/[0-9]{4}\.(0[1-9]|1[0-2])\.[0-9]+$ ]]
        ;;
    3044519)
        test "$FORGE_SITE_ROOT" = /home/forge/thelaravelarchitect.com
        test "${FORGE_VAR_SOURCE_BRANCH:-}" = main
        ;;
    *) exit 1 ;;
esac
[[ "${FORGE_VAR_REVISION:-}" =~ ^[a-f0-9]{40}$ ]]
test "$FORGE_DEPLOY_COMMIT" = "$FORGE_VAR_REVISION"

$CREATE_RELEASE()
cd $FORGE_RELEASE_DIRECTORY

if test "$(git rev-parse --is-shallow-repository)" = true; then
    git fetch --unshallow origin
fi
git fetch --no-tags origin "$FORGE_VAR_SOURCE_BRANCH:refs/remotes/origin/$FORGE_VAR_SOURCE_BRANCH"
test "$(git rev-parse "origin/$FORGE_VAR_SOURCE_BRANCH")" = "$FORGE_VAR_REVISION"
git checkout --detach "$FORGE_VAR_REVISION"
test "$(git rev-parse HEAD)" = "$FORGE_VAR_REVISION"
test ! -e public/deployment.json

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

export NODE_OPTIONS="--max-old-space-size=1024"
npm ci --production=false
npm run build

# Recreate the public storage link in the new release
rm -f public/storage
$FORGE_PHP artisan storage:link

if test "$FORGE_SITE_ID" = 3044519; then
    $FORGE_PHP artisan app:verify-production --no-ansi

    # Releases with pending migrations need a fresh, verified backup first. Fail closed.
    tla_pending="$($FORGE_PHP artisan migrate:status --pending --no-ansi)"
    if [[ "$tla_pending" != *"No pending migrations"* ]]; then
        tla_backup_verified=false
        for tla_attempt in 1 2; do
            $FORGE_PHP artisan backup:run --no-ansi
            if $FORGE_PHP artisan app:verify-backup --no-ansi; then
                tla_backup_verified=true
                break
            fi
        done
        test "$tla_backup_verified" = true
    fi
fi
$FORGE_PHP artisan optimize
# Migrate last: once the schema has changed, nothing may stop the release from activating.
$FORGE_PHP artisan migrate --force

# Records the deploy in Nightwatch; a failure here must not block activation.
$FORGE_PHP artisan nightwatch:deploy "$FORGE_DEPLOY_COMMIT" --ref="$FORGE_DEPLOY_COMMIT" \
    || echo "nightwatch:deploy failed; check the deployment marker in Nightwatch." >&2

$ACTIVATE_RELEASE()
$RESTART_QUEUES()

# Allow the real scheduler and worker to populate heartbeat checks after cache changes.
tla_verified=false
for tla_attempt in $(seq 1 24); do
    if $FORGE_PHP artisan app:verify-deployment "$FORGE_VAR_REVISION" --no-ansi; then
        tla_verified=true
        break
    fi
    sleep 5
done
test "$tla_verified" = true
test "$(readlink -f "$FORGE_SITE_PATH")" = "$(pwd -P)"
[[ "$FORGE_DEPLOYMENT_ID" =~ ^[1-9][0-9]*$ ]]
# This is the completion signal. Publish only after activation and verification.
printf '{"revision":"%s","deployment_id":"%s"}\n' \
    "$FORGE_VAR_REVISION" "$FORGE_DEPLOYMENT_ID" > public/deployment.json.tmp
mv public/deployment.json.tmp public/deployment.json
```

## Why the steps are in this order

### Backup gate

The backup gate runs only on production, and only when the release has pending
migrations, so ordinary deploys are not slowed. Staging is skipped because it
has no B2 destination.

It takes a new backup and verifies it straight away. A write between the two
commands can make the verification fail, so it repeats the pair once before
stopping the deployment. If both attempts fail, the script exits before
`migrate --force` and the previous release keeps serving traffic. Look at the
failure output, fix the cause, and redeploy. A failed `migrate:status` also
stops the deploy, because the assignment runs under `set -e`.

### Activation

`$ACTIVATE_RELEASE()` is required for Forge zero-downtime deployments. Without
it, Forge can report that a deployment completed while `current` still points to
the previous release. Keep activation after all preparation steps so a failed
build or check leaves the previous release serving traffic.

### Migrating last

`migrate --force` must stay the last step that can stop the deploy before
activation. The migration changes the shared live database, so once it has run
the new release has to go live: a failure after it would leave the previous
release serving a schema it was not written for.

- The asset install and build and `storage:link` therefore run first, before the
  backup gate, and a failed `npm ci` or `npm run build` stops the deploy with
  the database untouched.
- `nightwatch:deploy` runs after the migration, so it only records a deploy
  whose migrations succeeded. Its `|| echo` keeps a Nightwatch failure from
  blocking activation (the package already exits successfully on API errors;
  this also covers a crash). Confirm the marker in the Nightwatch dashboard as
  described under
  [Nightwatch deployment tracking](observability.md#nightwatch-deployment-tracking).
- `$RESTART_QUEUES()` must follow activation so long-running workers are
  restarted against the active release.

See the [Forge deployment documentation](https://laravel.com/forge/docs/sites/deployments#release-creation-and-activation).
