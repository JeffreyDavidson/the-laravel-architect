import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e/production',
    fullyParallel: false,
    forbidOnly: true,
    retries: 1,
    timeout: 30_000,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL: process.env.PRODUCTION_BASE_URL ?? 'https://thelaravelarchitect.com',
        navigationTimeout: 15_000,
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        ...devices['Desktop Chrome'],
    },
});
