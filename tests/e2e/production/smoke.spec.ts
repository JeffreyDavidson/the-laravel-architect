import { expect, test } from '@playwright/test';

const publicRoutes = ['/', '/projects', '/privacy'];

test('critical production routes are available', async ({ page, request }) => {
    await expect((await request.get('/up')).status()).toBe(200);

    for (const route of publicRoutes) {
        const response = await page.goto(route, { waitUntil: 'domcontentloaded' });

        expect(response?.status()).toBe(200);
        await expect(page.locator('body')).toBeVisible();
    }
});

test('the admin entry point redirects to authentication', async ({ request }) => {
    const response = await request.get('/admin', { maxRedirects: 0 });

    expect(response.status()).toBe(302);
    expect(response.headers().location).toContain('/admin/login');
});

test('production responses include the required security headers', async ({ request }) => {
    for (const route of publicRoutes) {
        const response = await request.get(route);
        const headers = response.headers();
        const frameOptions = (headers['x-frame-options'] ?? '')
            .split(',')
            .map((value) => value.trim());

        expect(headers['content-security-policy']).toContain("frame-ancestors 'self'");
        expect(headers['strict-transport-security']).toContain('max-age=31536000');
        expect(frameOptions.every((value) => value === 'SAMEORIGIN')).toBe(true);
        expect(headers['referrer-policy']).toBe('strict-origin-when-cross-origin');
    }
});
