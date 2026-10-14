import assert from 'node:assert/strict';
import test from 'node:test';
import { spawnSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

const gate = new URL('./verify-ci-jobs.mjs', import.meta.url);
const results = { quality: { result: 'success' }, tests: { result: 'success' }, browser: { result: 'success' } };
const run = (value) => spawnSync(process.execPath, [gate.pathname], {
    env: { ...process.env, CI_JOB_RESULTS: value }, encoding: 'utf8',
});

test('CI gate accepts only success from every required lane', () => {
    assert.equal(run(JSON.stringify(results)).status, 0);
});

for (const lane of Object.keys(results)) {
    for (const result of ['failure', 'cancelled', 'skipped', 'neutral', 'pending', '', null]) {
        test(`CI gate rejects ${lane} with result ${result}`, () => {
            const execution = run(JSON.stringify({ ...results, [lane]: { result } }));
            assert.equal(execution.status, 1);
            assert.match(execution.stderr, new RegExp(lane));
        });
    }
    test(`CI gate rejects missing ${lane}`, () => {
        const incomplete = { ...results };
        delete incomplete[lane];
        assert.equal(run(JSON.stringify(incomplete)).status, 1);
    });
}

for (const invalid of ['', '{', 'null', '[]', '{}']) {
    test(`CI gate rejects invalid or incomplete input ${invalid}`, () => {
        assert.equal(run(invalid).status, 1);
    });
}

test('CI waits for all independent lanes and retains the release success boundary', () => {
    const ci = readFileSync(new URL('../.github/workflows/ci.yml', import.meta.url), 'utf8');
    assert.match(ci, /laravel:\n\s+name: Laravel\n\s+needs: \[quality, tests, browser\]\n\s+if: \$\{\{ always\(\) \}\}/);
    assert.match(ci, /CI_JOB_RESULTS: \$\{\{ toJSON\(needs\) \}\}/);
    assert.match(ci, /node scripts\/verify-ci-jobs\.mjs/);
    assert.doesNotMatch(ci, /continue-on-error: true|--tia|--filter|--shard/);
    const staging = readFileSync(new URL('../.github/workflows/deploy-staging.yml', import.meta.url), 'utf8');
    assert.match(staging, /workflows: \[CI\]/);
    assert.match(staging, /workflow_run\.conclusion == 'success'/);
});
