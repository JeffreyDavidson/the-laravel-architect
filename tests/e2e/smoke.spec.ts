import { expect, test } from '@playwright/test';
import { assertNoHighImpactAccessibilityViolations } from './support/accessibility';
import { E2E_ADMIN_EMAIL, E2E_ADMIN_PASSWORD } from './support/admin';

const publicRoutes = [
    '/',
    '/about',
    '/services',
    '/blog',
    '/blog/e2e-code-example',
    '/contact',
    '/podcasts',
    '/privacy',
    '/projects',
    '/projects/e2e-project',
    '/uses',
];
const publicColorSchemes = ['light', 'dark'] as const;
const optionalPublicBundles = ['about', 'alpine', 'blog', 'home', 'podcast', 'prism'];

test.beforeEach(async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
});

function trackRequestedAssets(page: Page): string[] {
    const assets: string[] = [];

    page.on('request', (request) => {
        if (request.resourceType() !== 'script') {
            return;
        }

        assets.push(new URL(request.url()).pathname.split('/').pop() ?? '');
    });

    return assets;
}

function requestedBundle(assets: string[], bundle: string): boolean {
    return assets.some((asset) => asset === `${bundle}.js` || asset.startsWith(`${bundle}-`));
}

for (const colorScheme of publicColorSchemes) {
    for (const route of publicRoutes) {
        test(`${route} loads in ${colorScheme} mode without high-impact accessibility violations`, async ({ page }) => {
            await page.emulateMedia({ colorScheme });

            const response = await page.goto(route);

            expect(response?.ok()).toBeTruthy();
            await expect(page.locator('body')).toBeVisible();
            await assertNoHighImpactAccessibilityViolations(page);
        });
    }
}

test('admin login loads with labeled credentials without high-impact accessibility violations', async ({ page }) => {
    await page.emulateMedia({ colorScheme: 'dark' });

    const response = await page.goto('/admin/login');

    expect(response?.ok()).toBeTruthy();
    await expect(page.locator('input[type="email"]')).toHaveAccessibleName(/email/i);
    await expect(page.locator('input[type="password"]')).toHaveAccessibleName(/password/i);

    await assertNoHighImpactAccessibilityViolations(page);
});

test('homepage primary actions remain visible at a laptop viewport height', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/');

    for (const name of ['Discuss a Project', 'View Projects']) {
        const link = page.locator('[data-home-hero]').getByRole('link', { name, exact: true });

        await expect(link).toBeVisible();
        await expect
            .poll(async () => {
                const box = await link.boundingBox();

                return box !== null && box.y + box.height <= 720;
            })
            .toBeTruthy();
    }
});

test('homepage services stay concise and link to services', async ({ page }) => {
    await page.goto('/');
    const services = page.getByRole('region', { name: 'Where I can help' });
    await expect(services).toBeVisible();
    await expect(services.locator('dt')).toHaveCount(3);
    await expect(services.locator('svg[aria-hidden="true"]')).toHaveCount(3);
    await services.getByRole('link', { name: 'Explore services' }).click();
    await expect(page).toHaveURL(/\/services$/);
});

test('about card can be flipped with the keyboard', async ({ page }) => {
    await page.goto('/about');

    const card = page.getByRole('button', {
        name: 'Flip Jeffrey Davidson developer card',
    });

    await expect(card).toHaveAttribute('aria-pressed', 'false');
    await card.focus();
    await page.keyboard.press('Enter');
    await expect(card).toHaveAttribute('aria-pressed', 'true');
});

test('Alpine stays off pages with native interactions', async ({ page }) => {
    await page.goto('/');

    await expect.poll(() => page.evaluate(() => typeof window.Alpine)).toBe('undefined');

    await page.goto('/about');

    await expect.poll(() => page.evaluate(() => typeof window.Alpine)).toBe('undefined');

    await page.goto('/blog');

    await expect.poll(() => page.evaluate(() => typeof window.Alpine)).toBe('undefined');
});

test('blog posts can be searched and reset without Alpine', async ({ page }) => {
    await page.goto('/blog');

    const search = page.getByRole('searchbox', { name: 'Search posts' });
    const matchingPost = page.getByRole('link', {
        name: 'E2E Searchable Post',
        exact: true,
    });
    const otherPost = page.getByRole('link', {
        name: 'E2E Welcome Post',
        exact: true,
    });

    await search.fill('searchable post');
    await search.press('Enter');
    await expect(page).toHaveURL(/\/blog\?q=searchable(?:%20|\+)post$/);
    await expect(matchingPost).toBeVisible();
    await expect(otherPost).toBeHidden();

    await page.getByRole('link', { name: 'Clear search' }).click();
    await expect(page).toHaveURL(/\/blog$/);
    await expect(page.getByRole('link', { name: 'E2E Welcome Post', exact: true })).toBeVisible();
});

test('blog archive search, reset, and category links work without JavaScript', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false });
    const page = await context.newPage();

    await page.goto('/blog');
    await page.getByRole('searchbox', { name: 'Search posts' }).fill('searchable post');
    await page.getByRole('searchbox', { name: 'Search posts' }).press('Enter');
    await expect(page).toHaveURL(/\/blog\?q=searchable(?:%20|\+)post$/);
    await expect(page.getByRole('link', { name: 'E2E Searchable Post', exact: true })).toBeVisible();

    await page.getByRole('link', { name: 'Clear search' }).click();
    await expect(page).toHaveURL(/\/blog$/);
    await page.getByRole('link', { name: /E2E Laravel 2/ }).click();
    await expect(page).toHaveURL(/\/blog\?category=e2e-laravel$/);

    await context.close();
});

test('blog category filtering preserves the editorial hierarchy', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await page.goto('/blog');

    const laravelFilter = page.getByRole('link', { name: /E2E Laravel 2/ });

    await laravelFilter.click();

    await expect(page).toHaveURL(/\/blog\?category=e2e-laravel$/);
    await expect(laravelFilter).toHaveAttribute('aria-current', 'page');
    await expect(page.locator('[data-blog-post]')).toHaveCount(2);
});

test('static public pages avoid downloading optional interaction bundles', async ({ page }) => {
    const requestedAssets = trackRequestedAssets(page);

    await page.goto('/privacy');
    await expect(page.getByRole('main')).toBeVisible();

    for (const bundle of optionalPublicBundles) {
        expect(requestedBundle(requestedAssets, bundle), `${bundle} should not load on /privacy`).toBeFalsy();
    }
});

test('blog code blocks expose a keyboard-accessible copy action', async ({ page }) => {
    await page.goto('/blog/e2e-code-example');

    const copyButton = page.getByRole('button', { name: 'Copy code' }).first();

    await copyButton.focus();
    await expect(copyButton).toBeVisible();
    await copyButton.click();
    await expect(copyButton).toHaveAccessibleName('Copied');
});

test('blog syntax highlighting waits until the browser is idle', async ({ page }) => {
    await page.addInitScript(() => {
        const idleCallbacks: IdleRequestCallback[] = [];
        const testWindow = window as Window & {
            __testIdleCallbacks: IdleRequestCallback[];
        };

        Object.defineProperty(testWindow, '__testIdleCallbacks', {
            value: idleCallbacks,
        });
        Object.defineProperty(window, 'requestIdleCallback', {
            value: (callback) => {
                idleCallbacks.push(callback);

                return idleCallbacks.length;
            },
        });
    });

    await page.goto('/blog/e2e-code-example');

    await expect(page.locator('html')).toHaveAttribute('data-code-highlighting-state', 'idle');
    await expect(page.locator('.prose code .token')).toHaveCount(0);

    await page.evaluate(() => {
        const testWindow = window as Window & {
            __testIdleCallbacks: IdleRequestCallback[];
        };
        const callback = testWindow.__testIdleCallbacks.shift();

        callback?.({ didTimeout: false, timeRemaining: () => 50 });
    });

    await expect(page.locator('html')).toHaveAttribute('data-code-highlighting-state', 'ready');
    await expect(page.locator('.prose code .token').first()).toBeAttached();
});

test('an administrator can reach the dashboard', async ({ page }) => {
    await page.emulateMedia({ colorScheme: 'dark' });

    await page.goto('/admin/login');
    await page.locator('input[type="email"]').fill(E2E_ADMIN_EMAIL);
    await page.locator('input[type="password"]').fill(E2E_ADMIN_PASSWORD);
    await page.locator('button[type="submit"]').click();

    await expect(page).toHaveURL(/\/admin\/?$/);
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Posts', exact: true })).toBeVisible();

    await assertNoHighImpactAccessibilityViolations(page);
});
