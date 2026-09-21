import assert from 'node:assert/strict';
import { spawnSync } from 'node:child_process';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

const script = fileURLToPath(new URL('./forge-deployment.mjs', import.meta.url));
const revision = 'a'.repeat(40);

// Run the real CLI with only its external transport replaced. Never use real
// credentials or contact Forge / Cloudflare from the regression suite.
const transportStub = `data:text/javascript,${encodeURIComponent(String.raw`
    import assert from 'node:assert/strict';
    import childProcess from 'node:child_process';
    import { syncBuiltinESMExports } from 'node:module';

    let requests = 0;
    childProcess.spawnSync = (command, args) => {
        requests++;
        assert.equal(command, 'curl');
        assert.equal(args[args.indexOf('--request') + 1], 'GET');
        assert.equal(args.at(-1), 'https://staging.thelaravelarchitect.com/deployment.json');
        assert.equal(args.includes('--location'), false);
        assert.equal(args.includes('-L'), false);
        assert.equal(args.includes('CF-Access-Client-Id: test-client.access'), true);
        assert.equal(args.includes('CF-Access-Client-Secret: test-secret'), true);

        const status = process.env.TEST_HTTP_STATUS;
        return {
            status: JSON.parse(process.env.TEST_CURL_EXIT),
            stdout: 'HTTP/1.1 ' + status + '\r\n' +
                'cache-control: no-store\r\n' +
                'location: https://access.example.test/login?token=redirect-private-token\r\n' +
                '\r\n{}\n__DEPLOYMENT_STATUS__:' + status + '\n',
            stderr: 'sensitive-curl-output',
        };
    };
    syncBuiltinESMExports();
    globalThis.fetch = async () => { throw new Error('Unexpected network request.'); };
    process.on('exit', () => {
        if (requests !== Number(process.env.TEST_EXPECTED_REQUESTS)) {
            console.error('Unexpected transport request count.');
            process.exitCode = 1;
        }
    });
`)}`;

function diagnose({ status = 200, curlExit = 0, clientId = 'test-client.access', clientSecret = 'test-secret' } = {}) {
    const result = spawnSync(process.execPath, ['--import', transportStub, script, 'diagnose', 'staging', revision], {
        encoding: 'utf8',
        timeout: 5000,
        env: {
            PATH: '',
            CF_ACCESS_CLIENT_ID: clientId,
            CF_ACCESS_CLIENT_SECRET: clientSecret,
            TEST_HTTP_STATUS: String(status),
            TEST_CURL_EXIT: String(curlExit),
            TEST_EXPECTED_REQUESTS: clientId && clientSecret ? '1' : '0',
        },
    });

    assert.equal(result.error, undefined);
    assert.equal(result.signal, null);
    for (const sensitive of ['test-client.access', 'test-secret', 'redirect-private-token', 'sensitive-curl-output']) {
        assert.equal((result.stdout + result.stderr).includes(sensitive), false);
    }

    return { ...result, diagnostics: JSON.parse(result.stdout) };
}

test('CLI forwards environment credentials to the diagnostic GET and exits successfully on HTTP 200', () => {
    const result = diagnose();

    assert.equal(result.status, 0, result.stderr);
    assert.equal(result.stderr, '');
    assert.equal(result.diagnostics.credentials.clientId.present, true);
    assert.equal(result.diagnostics.credentials.clientId.length, 18);
    assert.equal(result.diagnostics.credentials.clientSecret.present, true);
    assert.equal(result.diagnostics.credentials.clientSecret.length, 11);
    assert.equal(result.diagnostics.response.status, 200);
});

for (const [name, credentials] of [
    ['client ID', { clientId: '' }],
    ['client secret', { clientSecret: '' }],
    ['both credentials', { clientId: '', clientSecret: '' }],
]) {
    test(`CLI fails without making an HTTP request with missing ${name}`, () => {
        const result = diagnose(credentials);

        assert.equal(result.status, 1);
        assert.equal(result.stderr.trim(), 'Staging requires both Cloudflare Access credentials.');
        assert.equal(result.diagnostics.credentials.clientId.present, credentials.clientId !== '');
        assert.equal(result.diagnostics.credentials.clientSecret.present, credentials.clientSecret !== '');
        assert.equal(result.diagnostics.response, undefined);
    });
}

for (const status of [204, 301, 302, 303, 307, 308, 401, 403, 404, 500]) {
    test(`CLI reports supplied credentials but fails on HTTP ${status}`, () => {
        const result = diagnose({ status });

        assert.equal(result.status, 1);
        assert.equal(result.diagnostics.credentials.clientId.present, true);
        assert.equal(result.diagnostics.credentials.clientSecret.present, true);
        assert.equal(result.diagnostics.response.status, status);
        assert.match(result.stderr, new RegExp(`Cloudflare Access diagnostic returned HTTP ${status}`));
        if (status >= 300 && status < 400) {
            assert.match(result.stderr, /redirect.*service-token authentication was not confirmed/);
        }
    });
}

for (const [curlExit, code] of [
    [28, 'CURL_EXIT_28'],
    [null, 'CURL_FAILED'],
]) {
    test(`CLI fails on ${code} even after receiving HTTP 200 headers`, () => {
        const result = diagnose({ curlExit });

        assert.equal(result.status, 1);
        assert.equal(result.diagnostics.credentials.clientId.present, true);
        assert.equal(result.diagnostics.credentials.clientSecret.present, true);
        assert.equal(result.diagnostics.response, undefined);
        assert.match(result.stderr, new RegExp(`Deployment request failed.*${code}`));
    });
}
