import { randomUUID } from 'node:crypto';
import { setTimeout as delay } from 'node:timers/promises';
import { pathToFileURL } from 'node:url';

const sites = {
    staging: { id: '3366565', origin: 'https://staging.thelaravelarchitect.com' },
    production: { id: '3044519', origin: 'https://thelaravelarchitect.com' },
};

export function validateRevision(revision) {
    if (!/^[a-f0-9]{40}$/.test(revision ?? '')) {
        throw new Error('A full lowercase commit SHA is required.');
    }
    return revision;
}

function siteFor(environment) {
    if (!Object.hasOwn(sites, environment)) {
        throw new Error('Unknown deployment environment.');
    }
    return sites[environment];
}

export function requestHeaders(environment, options) {
    siteFor(environment);
    const headers = { Accept: 'application/json', 'Cache-Control': 'no-cache, no-store' };
    if (environment === 'staging') {
        if (!options.clientId || !options.clientSecret) {
            throw new Error('Staging requires both Cloudflare Access credentials.');
        }
        headers['CF-Access-Client-Id'] = options.clientId;
        headers['CF-Access-Client-Secret'] = options.clientSecret;
    }
    return headers;
}

async function request(url, init, options) {
    try {
        return await (options.fetch ?? fetch)(url, {
            ...init,
            // Keep deployment-trigger redirects observable without following them.
            // Following a redirect could turn a failed hook into an unrelated request.
            redirect: init.method === 'POST' ? 'manual' : 'error',
            signal: AbortSignal.timeout(15000),
        });
    } catch {
        // URLs can contain the Forge hook token; never report the underlying error.
        throw new Error('Deployment request failed; inspect Forge before retrying a trigger.');
    }
}

export async function readMarker(environment, options = {}, allowMissing = false) {
    const response = await request(
        `${siteFor(environment).origin}/deployment.json?check=${randomUUID()}`,
        { headers: requestHeaders(environment, options) },
        options,
    );
    if (allowMissing && response.status === 404) {
        return null;
    }
    if (response.status !== 200) {
        throw new Error(`Release marker returned HTTP ${response.status}.`);
    }
    if (
        !/(?:^|,)\s*no-store\s*(?:,|$)/i.test(response.headers.get('cache-control') ?? '') ||
        Number(response.headers.get('age') ?? 0) > 0 ||
        /^(HIT|STALE|UPDATING|REVALIDATED)$/i.test(response.headers.get('cf-cache-status') ?? '')
    ) {
        throw new Error('Release marker must be served uncached with Cache-Control: no-store.');
    }
    let marker;
    try {
        marker = await response.json();
    } catch {
        throw new Error('Release marker is not JSON.');
    }
    validateRevision(marker?.revision);
    if (!/^[1-9][0-9]*$/.test(String(marker?.deployment_id ?? ''))) {
        throw new Error('Release marker has no valid Forge deployment ID.');
    }
    return marker;
}

export async function verifyRelease(environment, revision, options = {}) {
    validateRevision(revision);
    const marker = await readMarker(environment, options);
    if (marker.revision !== revision) {
        throw new Error('The serving revision does not match the approved commit.');
    }
    const response = await request(
        `${siteFor(environment).origin}/up?check=${randomUUID()}`,
        { headers: requestHeaders(environment, options) },
        options,
    );
    if (response.status !== 200) {
        throw new Error(`Application health returned HTTP ${response.status}.`);
    }
    return marker;
}

export function deploymentHook(environment, revision, hook) {
    validateRevision(revision);
    const site = siteFor(environment);
    let url;
    try {
        url = new URL(hook);
    } catch {
        throw new Error('A Forge deployment hook is required.');
    }
    if (
        url.origin !== 'https://forge.laravel.com' ||
        url.username ||
        url.password ||
        url.pathname !== `/servers/753072/sites/${site.id}/deploy/http` ||
        !url.searchParams.get('token') ||
        url.hash
    ) {
        throw new Error('Forge hook does not belong to the intended server and site.');
    }
    url.searchParams.set('forge_deploy_branch', 'main');
    url.searchParams.set('forge_deploy_commit', revision);
    // Unlike the metadata label above, the Forge script uses this to pin checkout.
    url.searchParams.set('revision', revision);
    return url;
}

export async function deployRelease(environment, revision, options = {}) {
    const hook = deploymentHook(environment, revision, options.hook);
    // Fail before triggering anything when Access credentials are absent.
    requestHeaders(environment, options);
    if (environment === 'production') {
        await verifyRelease('staging', revision, options);
    }
    const previous = await readMarker(environment, options, true);
    const response = await request(hook, { method: 'POST' }, options);
    if (!response.ok) {
        throw new Error(`Forge rejected the deployment trigger (HTTP ${response.status}).`);
    }
    // Never retry the mutation. A retry could create an overlapping deployment.
    const now = options.now ?? Date.now;
    const deadline = now() + 11 * 60 * 1000;
    while (now() < deadline) {
        await (options.delay ?? delay)(10000);
        const marker = await readMarker(environment, options, true);
        if (marker?.revision === revision && String(marker.deployment_id) !== String(previous?.deployment_id)) {
            return await verifyRelease(environment, revision, options);
        }
    }
    throw new Error('Deployment completion was not verified within 11 minutes; inspect Forge before retrying.');
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
    try {
        const [operation, environment, revision] = process.argv.slice(2);
        const options = {
            hook: process.env.FORGE_DEPLOY_HOOK,
            clientId: process.env.CF_ACCESS_CLIENT_ID,
            clientSecret: process.env.CF_ACCESS_CLIENT_SECRET,
        };
        if (!['deploy', 'verify'].includes(operation)) {
            throw new Error('Use deploy or verify with an environment and full commit SHA.');
        }
        const result = await (operation === 'deploy' ? deployRelease : verifyRelease)(environment, revision, options);
        console.log(`Verified ${environment}: ${result.revision}, Forge deployment ${result.deployment_id}.`);
    } catch (error) {
        console.error(error.message);
        process.exitCode = 1;
    }
}
