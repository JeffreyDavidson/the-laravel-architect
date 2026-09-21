import assert from 'node:assert/strict';
import test from 'node:test';
import {
    accessCredentialDiagnostics,
    diagnoseAccess,
    diagnosticFailureReason,
    deployRelease,
    deploymentHook,
    readMarker,
    requestHeaders,
    transportFailureMetadata,
    transportFailureReason,
    validateRevision,
    verifyRelease,
} from './forge-deployment.mjs';

const revision = 'a'.repeat(40);
const access = { clientId: 'test-client', clientSecret: 'test-secret' };
const hook = 'https://forge.laravel.com/servers/753072/sites/3366565/deploy/http?token=test-token';
const marker = (id = 1, sha = revision) =>
    Response.json({ revision: sha, deployment_id: id }, { headers: { 'Cache-Control': 'no-store' } });

test('requires full immutable revisions', () => {
    for (const invalid of ['main', 'abc123', '', undefined, 'A'.repeat(40), `a;${revision}`]) {
        assert.throws(() => validateRevision(invalid));
    }
    assert.equal(validateRevision(revision), revision);
});

test('requires staging credentials and never includes them in production requests', () => {
    assert.throws(() => requestHeaders('staging', {}));
    assert.throws(() => requestHeaders('staging', { clientId: 'only-one' }));
    assert.throws(() => requestHeaders('unknown', access));
    assert.equal(requestHeaders('staging', access)['CF-Access-Client-Secret'], 'test-secret');
    assert.equal(requestHeaders('production', access)['CF-Access-Client-Secret'], undefined);
});

test('reports safe Access credential diagnostics without exposing values', () => {
    const diagnostics = accessCredentialDiagnostics({
        clientId: 'client-id.access',
        clientSecret: 'secret-value',
    });

    assert.deepEqual(diagnostics, {
        clientId: {
            present: true,
            length: 16,
            hasWhitespace: false,
            hasLeadingOrTrailingWhitespace: false,
            hasHeaderPrefix: false,
            formatLooksValid: true,
        },
        clientSecret: {
            present: true,
            length: 12,
            hasWhitespace: false,
            hasLeadingOrTrailingWhitespace: false,
            hasHeaderPrefix: false,
        },
    });
});

test('uses the stable health endpoint for Access diagnostics', async () => {
    let requestedUrl;

    await diagnoseAccess('staging', {
        ...access,
        fetch: async url => {
            requestedUrl = url;

            return new Response('', { status: 200 });
        },
    });

    assert.equal(requestedUrl, 'https://staging.thelaravelarchitect.com/up');
});

test('fails the Access diagnostic for unavailable or rejected staging credentials', () => {
    assert.equal(
        diagnosticFailureReason('staging', {
            credentials: accessCredentialDiagnostics(),
            error: 'Staging requires both Cloudflare Access credentials.',
        }),
        'Staging requires both Cloudflare Access credentials.',
    );
    assert.equal(
        diagnosticFailureReason('staging', {
            credentials: accessCredentialDiagnostics(access),
            response: { status: 403 },
        }),
        'Cloudflare Access diagnostic returned HTTP 403.',
    );
    assert.equal(
        diagnosticFailureReason('production', {
            credentials: accessCredentialDiagnostics(),
            error: 'ignored for production',
        }),
        null,
    );
});

test('requires an HTTP 200 response before the staging diagnostic can succeed', () => {
    for (const status of [undefined, null, 0, 99, 600, '200', 204, 302, 403, 500]) {
        const failure = diagnosticFailureReason('staging', {
            credentials: accessCredentialDiagnostics(access),
            response: { status },
        });

        assert.equal(typeof failure, 'string', `Unexpected success for status ${status}`);
    }

    assert.equal(
        diagnosticFailureReason('staging', {
            credentials: accessCredentialDiagnostics(access),
            response: { status: 200 },
        }),
        null,
    );
});

test('constrains hook credentials to the exact Forge target and supplies a separate checkout revision', () => {
    for (const invalid of [
        hook.replace('https:', 'http:'),
        hook.replace('forge.laravel.com', 'example.com'),
        hook.replace('3366565', '3044519'),
        hook.replace('753072', '1'),
        hook.split('?')[0],
    ]) {
        assert.throws(() => deploymentHook('staging', revision, invalid));
    }
    const url = deploymentHook('staging', revision, hook);
    assert.equal(url.searchParams.get('revision'), revision);
    assert.equal(url.searchParams.get('forge_deploy_commit'), revision);
    assert.equal(url.searchParams.get('forge_deploy_branch'), 'main');
});

test('rejects redirects and hides transport errors that could contain credentials', async () => {
    await assert.rejects(
        readMarker('staging', {
            ...access,
            fetch: async (url, init) => {
                assert.equal(new URL(url).origin, 'https://staging.thelaravelarchitect.com');
                assert.equal(init.redirect, 'error');
                throw new Error('test-secret');
            },
        }),
        error => !error.message.includes('test-secret'),
    );
});

test('classifies transport failures without exposing error details', () => {
    assert.equal(transportFailureReason({ cause: { code: 'ENOTFOUND' } }), 'DNS resolution');
    assert.equal(transportFailureReason({ cause: { code: 'CERT_HAS_EXPIRED' } }), 'TLS negotiation');
    assert.equal(transportFailureReason({ name: 'TimeoutError' }), 'timeout');
    assert.equal(transportFailureReason({ cause: { code: 'ECONNRESET' } }), 'connection failure');
    assert.equal(transportFailureReason(new Error('test-secret')), 'network failure');
});

test('reports only safe transport metadata', () => {
    assert.equal(
        transportFailureMetadata({ name: 'TypeError', code: 'UND_ERR_SOCKET', cause: { code: 'ECONNRESET' } }),
        '; error=TypeError, code=UND_ERR_SOCKET, cause=ECONNRESET',
    );
    assert.equal(transportFailureMetadata(new Error('test-secret')), '; error=Error');
    assert.equal(transportFailureMetadata({ message: 'test-secret', code: 'not-safe' }), '');
});

test('reports a Forge hook redirect without following it', async () => {
    await assert.rejects(
        deployRelease('staging', revision, {
            ...access,
            hook,
            fetch: async (url, init) => {
                if (init.method === 'POST') {
                    assert.equal(init.redirect, 'manual');
                    return new Response('', { status: 302 });
                }

                return marker();
            },
        }),
        /Forge rejected the deployment trigger \(HTTP 302\)/,
    );
});

test('rejects missing, malformed and authentication-page release markers', async () => {
    for (const response of [
        new Response('', { status: 302 }),
        new Response('login', { headers: { 'Cache-Control': 'no-store' } }),
        marker(1, 'main'),
        marker('invalid'),
    ]) {
        await assert.rejects(readMarker('staging', { ...access, fetch: async () => response }));
    }
});

test('rejects a different revision before checking health', async () => {
    let calls = 0;
    await assert.rejects(
        verifyRelease('staging', revision, {
            ...access,
            fetch: async () => {
                calls++;
                return marker(1, 'b'.repeat(40));
            },
        }),
        /does not match/,
    );
    assert.equal(calls, 1);
});

test('rejects cached release evidence', async () => {
    for (const headers of [
        {},
        { 'Cache-Control': 'public, max-age=3600' },
        { 'Cache-Control': 'no-store', Age: '60' },
        { 'Cache-Control': 'no-store', 'CF-Cache-Status': 'HIT' },
    ]) {
        await assert.rejects(
            readMarker('staging', {
                ...access,
                fetch: async () => Response.json({ revision, deployment_id: 1 }, { headers }),
            }),
            /uncached/,
        );
    }
});

test('requires successful health as well as a matching revision', async () => {
    const responses = [marker(), new Response('', { status: 503 })];
    await assert.rejects(
        verifyRelease('staging', revision, { ...access, fetch: async () => responses.shift() }),
        /health/,
    );
});

test('waits for a new deployment ID and triggers Forge only once', async () => {
    const responses = [marker(1), new Response('accepted'), marker(1), marker(2), marker(2), new Response('healthy')];
    const requests = [];
    const result = await deployRelease('staging', revision, {
        ...access,
        hook,
        delay: async () => {},
        fetch: async (url, init) => {
            requests.push({ url, init });
            return responses.shift();
        },
    });
    assert.equal(result.deployment_id, 2);
    assert.equal(requests.filter(request => request.init.method === 'POST').length, 1);
    assert.equal(requests.find(request => request.init.method === 'POST').init.headers, undefined);
});

test('waits through transient release marker responses', async () => {
    const responses = [
        new Response('', { status: 400 }),
        new Response('accepted'),
        new Response('', { status: 400 }),
        marker(2),
        marker(2),
        new Response('healthy'),
    ];

    const result = await deployRelease('staging', revision, {
        ...access,
        hook,
        delay: async () => {},
        fetch: async () => responses.shift(),
    });

    assert.equal(result.deployment_id, 2);
});

test('uses curl for Forge triggers when configured', async () => {
    const responses = [marker(1), marker(2), marker(2), new Response('healthy')];
    let curlCalls = 0;

    const result = await deployRelease('staging', revision, {
        ...access,
        hook,
        triggerTransport: 'curl',
        delay: async () => {},
        curl: async (command, args) => {
            curlCalls++;
            assert.equal(command, 'curl');
            assert.deepEqual(args.slice(0, 2), ['--silent', '--show-error']);
            assert.equal(
                args.at(-1).startsWith('https://forge.laravel.com/servers/753072/sites/3366565/deploy/http?'),
                true,
            );
            return { stdout: '202' };
        },
        fetch: async () => responses.shift(),
    });

    assert.equal(result.deployment_id, 2);
    assert.equal(curlCalls, 1);
});

test('uses curl for release verification when configured', async () => {
    const body = JSON.stringify({ revision, deployment_id: 2 });
    const result = await readMarker('staging', {
        ...access,
        readTransport: 'curl',
        curl: async (command, args) => {
            assert.equal(command, 'curl');
            assert.equal(args.includes('--dump-header'), true);
            assert.equal(args.at(-1), 'https://staging.thelaravelarchitect.com/deployment.json');

            return {
                stdout: `HTTP/2 200\r\ncache-control: no-store\r\nage: 0\r\ncf-cache-status: MISS\r\n\r\n${body}\n__DEPLOYMENT_STATUS__:200\n`,
            };
        },
    });

    assert.deepEqual(result, { revision, deployment_id: 2 });
});

test('does not trigger production when staging no longer matches approval', async () => {
    let calls = 0;
    await assert.rejects(
        deployRelease('production', revision, {
            ...access,
            hook: hook.replace('3366565', '3044519'),
            fetch: async (url, init) => {
                calls++;
                assert.notEqual(init.method, 'POST');
                return marker(1, 'b'.repeat(40));
            },
        }),
        /does not match/,
    );
    assert.equal(calls, 1);
});

test('does not retry a rejected or uncertain Forge trigger', async () => {
    for (const rejected of [true, false]) {
        let posts = 0;
        await assert.rejects(
            deployRelease('staging', revision, {
                ...access,
                hook,
                fetch: async (url, init) => {
                    if (init.method !== 'POST') {
                        return marker();
                    }
                    posts++;
                    if (rejected) {
                        return new Response('', { status: 500 });
                    }
                    throw new Error(`Failed request to ${hook}`);
                },
            }),
            error => !error.message.includes('test-token'),
        );
        assert.equal(posts, 1);
    }
});

test('times out without retrying an accepted deployment', async () => {
    let time = 0;
    let posts = 0;
    await assert.rejects(
        deployRelease('staging', revision, {
            ...access,
            hook,
            now: () => time,
            delay: async () => {
                time += 12 * 60 * 1000;
            },
            fetch: async (url, init) => {
                if (init.method === 'POST') {
                    posts++;
                    return new Response('accepted');
                }
                return marker(1);
            },
        }),
        /11 minutes/,
    );
    assert.equal(posts, 1);
});
