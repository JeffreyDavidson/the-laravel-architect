import { spawnSync } from 'node:child_process';
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

export function transportFailureReason(error) {
    const code = error?.cause?.code ?? error?.code;

    if (['ENOTFOUND', 'EAI_AGAIN'].includes(code)) {
        return 'DNS resolution';
    }

    if (['CERT_HAS_EXPIRED', 'ERR_TLS_CERT_ALTNAME_INVALID', 'UNABLE_TO_VERIFY_LEAF_SIGNATURE'].includes(code)) {
        return 'TLS negotiation';
    }

    if (error?.name === 'AbortError' || error?.name === 'TimeoutError' || code === 'UND_ERR_CONNECT_TIMEOUT') {
        return 'timeout';
    }

    if (['ECONNREFUSED', 'ECONNRESET', 'ETIMEDOUT'].includes(code)) {
        return 'connection failure';
    }

    return 'network failure';
}

export function transportFailureMetadata(error) {
    const metadata = [];
    const name = typeof error?.name === 'string' ? error.name : null;
    const code = error?.code ?? null;
    const causeCode = error?.cause?.code ?? null;

    if (name && /^[A-Za-z][A-Za-z0-9]*$/.test(name)) {
        metadata.push(`error=${name}`);
    }

    if (typeof code === 'string' && /^[A-Z0-9_]+$/.test(code)) {
        metadata.push(`code=${code}`);
    }

    if (typeof causeCode === 'string' && /^[A-Z0-9_]+$/.test(causeCode)) {
        metadata.push(`cause=${causeCode}`);
    }

    return metadata.length > 0 ? `; ${metadata.join(', ')}` : '';
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

export function accessCredentialDiagnostics(options = {}) {
    const inspect = value => {
        const text = typeof value === 'string' ? value : '';

        return {
            present: text.length > 0,
            length: text.length,
            hasWhitespace: /\s/.test(text),
            hasLeadingOrTrailingWhitespace: text !== text.trim(),
            hasHeaderPrefix: /^CF-Access-Client-(?:Id|Secret):/i.test(text),
        };
    };

    return {
        clientId: {
            ...inspect(options.clientId),
            formatLooksValid: /^[A-Za-z0-9._-]+$/.test(options.clientId ?? ''),
        },
        clientSecret: inspect(options.clientSecret),
    };
}

export function diagnosticFailureReason(environment, diagnostics) {
    if (environment !== 'staging') {
        return null;
    }

    if (diagnostics.error) {
        return diagnostics.error;
    }

    const credentials = diagnostics.credentials ?? {};
    if (!credentials.clientId?.present || !credentials.clientSecret?.present) {
        return 'Staging requires both Cloudflare Access credentials.';
    }

    if (
        credentials.clientId.hasWhitespace ||
        credentials.clientSecret.hasWhitespace ||
        credentials.clientId.hasHeaderPrefix ||
        credentials.clientSecret.hasHeaderPrefix ||
        !credentials.clientId.formatLooksValid
    ) {
        return 'Staging Cloudflare Access credentials have an invalid format.';
    }

    const status = diagnostics.response?.status;
    if (!Number.isInteger(status) || status < 100 || status > 599) {
        return 'Cloudflare Access diagnostic did not receive a valid HTTP status.';
    }
    if (status >= 300 && status < 400) {
        return `Cloudflare Access diagnostic returned HTTP ${status} (redirect); service-token authentication was not confirmed.`;
    }
    if (status !== 200) {
        return `Cloudflare Access diagnostic returned HTTP ${status}.`;
    }

    return null;
}

async function request(url, init, options) {
    if (
        (init.method === 'POST' && options.triggerTransport === 'curl') ||
        (init.method !== 'POST' && options.readTransport === 'curl')
    ) {
        return requestWithCurl(url, init, options);
    }

    try {
        return await (options.fetch ?? fetch)(url, {
            ...init,
            // Keep deployment-trigger redirects observable without following them.
            // Following a redirect could turn a failed hook into an unrelated request.
            redirect: init.method === 'POST' ? 'manual' : 'error',
            signal: AbortSignal.timeout(15000),
        });
    } catch (error) {
        // URLs can contain the Forge hook token; never report the underlying error.
        throw new Error(
            `Deployment request failed before an HTTP response (${transportFailureReason(error)}${transportFailureMetadata(error)}); inspect Forge before retrying a trigger.`,
        );
    }
}

async function requestWithCurl(url, init, options) {
    const args = [
        '--silent',
        '--show-error',
        '--http1.1',
        '--request',
        init.method ?? 'GET',
        '--connect-timeout',
        '10',
        '--max-time',
        '15',
    ];

    for (const [name, value] of Object.entries(init.headers ?? {})) {
        args.push('--header', `${name}: ${value}`);
    }

    if (init.method === 'POST') {
        args.push('--output', '/dev/null', '--write-out', '%{http_code}');
    } else {
        args.push('--dump-header', '-', '--output', '-', '--write-out', '\n__DEPLOYMENT_STATUS__:%{http_code}\n');
    }

    args.push(String(url));

    try {
        const { stdout } = await (options.curl ?? runCurl)('curl', args, { timeout: 15000 });
        const output = String(stdout ?? '');
        const statusMarker = output.match(/\n__DEPLOYMENT_STATUS__:(\d{3})\s*$/);
        const status = Number(statusMarker?.[1] ?? output.trim());

        if (!Number.isInteger(status) || status < 100) {
            throw new Error('Forge returned no valid HTTP status.');
        }

        if (init.method === 'POST') {
            return { status, ok: status >= 200 && status < 300 };
        }

        const responseOutput = output.slice(0, statusMarker?.index ?? output.length);
        const separator = responseOutput.indexOf('\r\n\r\n');
        const headerText = separator >= 0 ? responseOutput.slice(0, separator) : '';
        const body = separator >= 0 ? responseOutput.slice(separator + 4) : '';
        const headers = new Headers();
        for (const line of headerText.split(/\r?\n/).slice(1)) {
            const separatorIndex = line.indexOf(':');
            if (separatorIndex > 0) {
                headers.set(line.slice(0, separatorIndex), line.slice(separatorIndex + 1).trim());
            }
        }

        return {
            status,
            headers,
            json: async () => JSON.parse(body),
        };
    } catch (error) {
        // The hook URL contains a credential; never report curl's command or output.
        throw new Error(
            `Deployment request failed (${transportFailureReason(error)}${transportFailureMetadata(error)}); inspect Forge before retrying a trigger.`,
        );
    }
}

function runCurl(command, args, options) {
    const result = spawnSync(command, args, { ...options, encoding: 'utf8' });

    if (result.error) {
        throw result.error;
    }
    if (result.status !== 0) {
        const error = new Error('curl exited unsuccessfully.');
        error.code = Number.isInteger(result.status) ? `CURL_EXIT_${result.status}` : 'CURL_FAILED';
        throw error;
    }

    return { stdout: result.stdout };
}

export async function readMarker(environment, options = {}, allowMissing = false) {
    const response = await request(
        `${siteFor(environment).origin}/deployment.json`,
        { headers: requestHeaders(environment, options) },
        options,
    );
    if (allowMissing && [400, 404].includes(response.status)) {
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

export async function diagnoseAccess(environment, options = {}) {
    const diagnostics = { credentials: accessCredentialDiagnostics(options) };

    try {
        const response = await request(
            `${siteFor(environment).origin}/deployment.json`,
            { headers: requestHeaders(environment, options) },
            options,
        );

        diagnostics.response = {
            status: response.status,
            headers: Object.fromEntries(
                ['cache-control', 'cf-cache-status', 'cf-ray', 'content-type', 'server', 'www-authenticate']
                    .map(name => [name, response.headers.get(name)])
                    .filter(([, value]) => value !== null),
            ),
        };
    } catch (error) {
        diagnostics.error = error.message;
    }

    return diagnostics;
}

export async function verifyRelease(environment, revision, options = {}) {
    validateRevision(revision);
    const marker = await readMarker(environment, options);
    if (marker.revision !== revision) {
        throw new Error('The serving revision does not match the approved commit.');
    }
    const response = await request(
        `${siteFor(environment).origin}/up`,
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
    return waitForRelease(environment, revision, {
        ...options,
        previousDeploymentId: previous?.deployment_id,
    });
}

export async function waitForRelease(environment, revision, options = {}) {
    validateRevision(revision);
    // Never retry the mutation. A retry could create an overlapping deployment.
    const now = options.now ?? Date.now;
    const deadline = now() + 11 * 60 * 1000;
    while (now() < deadline) {
        await (options.delay ?? delay)(10000);
        const marker = await readMarker(environment, options, true);
        if (
            marker?.revision === revision &&
            (options.previousDeploymentId === undefined ||
                String(marker.deployment_id) !== String(options.previousDeploymentId))
        ) {
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
            triggerTransport: 'curl',
            readTransport: 'curl',
        };
        if (!['deploy', 'diagnose', 'verify', 'wait'].includes(operation)) {
            throw new Error('Use deploy, diagnose, verify, or wait with an environment and full commit SHA.');
        }
        if (operation === 'diagnose') {
            const result = await diagnoseAccess(environment, options);
            console.log(JSON.stringify(result));
            const failure = diagnosticFailureReason(environment, result);
            if (failure) {
                console.error(failure);
                process.exitCode = 1;
            }
        } else {
            const operationHandler = {
                deploy: deployRelease,
                verify: verifyRelease,
                wait: waitForRelease,
            }[operation];
            const result = await operationHandler(environment, revision, options);
            console.log(`Verified ${environment}: ${result.revision}, Forge deployment ${result.deployment_id}.`);
        }
    } catch (error) {
        console.error(error.message);
        process.exitCode = 1;
    }
}
