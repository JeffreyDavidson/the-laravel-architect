import assert from 'node:assert/strict';
import test from 'node:test';
import {
    deployRelease,
    deploymentHook,
    readMarker,
    requestHeaders,
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
