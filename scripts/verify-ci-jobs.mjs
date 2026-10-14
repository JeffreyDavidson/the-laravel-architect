// Fail closed: only explicit success from every required lane clears Laravel.
const required = ['quality', 'tests', 'browser'];

try {
    const results = JSON.parse(process.env.CI_JOB_RESULTS ?? '');
    if (results === null || typeof results !== 'object' || Array.isArray(results)) {
        throw new Error('CI job results must be an object.');
    }
    for (const lane of required) {
        if (results[lane]?.result !== 'success') {
            throw new Error(`${lane} did not succeed.`);
        }
    }
    console.log('Every required CI lane succeeded.');
} catch (error) {
    console.error(error instanceof SyntaxError ? 'CI job results are invalid JSON.' : error.message);
    process.exitCode = 1;
}
