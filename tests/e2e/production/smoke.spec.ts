import { expect, test } from '@playwright/test';

const baseUrl = new URL(process.env.PRODUCTION_BASE_URL ?? 'https://thelaravelarchitect.com');

const publicRoutes = [
    '/',
    '/about',
    '/archive',
    '/blog',
    '/contact',
    '/newsletter',
    '/newsletter/rss',
    '/podcasts',
    '/privacy',
    '/projects',
    '/rss',
    '/search?q=accessibility',
    '/services',
    '/sitemap.xml',
    '/uses',
];

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
    const redirectUrl = new URL(response.headers().location ?? '', baseUrl);
    const redirectsToAdminLogin = redirectUrl.origin === baseUrl.origin && redirectUrl.pathname === '/admin/login';
    const redirectsToCloudflareAccess =
        redirectUrl.protocol === 'https:' &&
        redirectUrl.hostname.endsWith('.cloudflareaccess.com') &&
        redirectUrl.pathname === `/cdn-cgi/access/login/${baseUrl.hostname}`;

    expect(response.status()).toBe(302);
    expect(redirectsToAdminLogin || redirectsToCloudflareAccess).toBe(true);
});

test('production responses include the required security headers', async ({ request }) => {
    for (const route of publicRoutes) {
        const response = await request.get(route);
        const headers = response.headers();
        const frameOptions = (headers['x-frame-options'] ?? '').split(',').map((value) => value.trim());
        const frameAncestors = headers['content-security-policy']
            ?.split(';')
            .map((directive) => directive.trim())
            .find((directive) => directive.startsWith('frame-ancestors '));

        expect(["frame-ancestors 'self'", "frame-ancestors 'none'"]).toContain(frameAncestors);
        expect(headers['strict-transport-security']).toContain('max-age=31536000');
        expect(frameOptions.length).toBeGreaterThan(0);
        expect(frameOptions.every((value) => ['SAMEORIGIN', 'DENY'].includes(value))).toBe(true);
        expect(headers['referrer-policy']).toBe('strict-origin-when-cross-origin');
    }
});
