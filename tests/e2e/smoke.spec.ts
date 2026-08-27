import AxeBuilder from '@axe-core/playwright';
import { expect, Page, test } from '@playwright/test';

const publicRoutes = [
    '/',
    '/about',
    '/blog',
    '/blog/how-i-structure-every-laravel-project',
    '/contact',
    '/podcasts',
    '/privacy',
    '/projects',
    '/projects/ringside',
    '/testimonials/submit',
    '/uses',
];
const publicColorSchemes = ['light', 'dark'] as const;
const optionalPublicBundles = ['about', 'alpine', 'architecture-scene', 'blog', 'home', 'podcast', 'prism'];

test.beforeEach(async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
});

async function assertNoHighImpactAccessibilityViolations(page: Page): Promise<void> {
    const results = await new AxeBuilder({ page }).analyze();
    const highImpactViolations = results.violations.filter(
        (violation) => violation.impact === 'critical' || violation.impact === 'serious',
    );

    expect(highImpactViolations.map((violation) => ({
        id: violation.id,
        impact: violation.impact,
        targets: violation.nodes.map((node) => node.target),
    }))).toEqual([]);
}

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
        const link = page.getByRole('main').getByRole('link', { name, exact: true });

        await expect(link).toBeVisible();
        await expect.poll(async () => {
            const box = await link.boundingBox();

            return box !== null && box.y + box.height <= 720;
        }).toBeTruthy();
    }
});

test('homepage architecture scene keeps an accessible fallback', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/');

    const scene = page.locator('[data-architecture-scene]');

    await expect(scene).toBeVisible();
    await expect(scene.locator('[data-architecture-fallback]')).toBeAttached();
    await expect.poll(async () => scene.getAttribute('data-architecture-state'))
        .toMatch(/ready|fallback/);
});

test('homepage reduced motion avoids downloading the decorative architecture scene', async ({ page }) => {
    const requestedAssets = trackRequestedAssets(page);

    await page.goto('/');
    await expect(page.locator('[data-architecture-scene]')).toHaveAttribute('data-architecture-state', 'fallback');

    expect(requestedBundle(requestedAssets, 'home')).toBeTruthy();
    expect(requestedBundle(requestedAssets, 'architecture-scene')).toBeFalsy();
});

test('homepage defers its decorative architecture scene until the browser is idle', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await page.addInitScript(() => {
        const idleCallbacks: IdleRequestCallback[] = [];
        const testWindow = window as Window & { __testIdleCallbacks: IdleRequestCallback[] };

        Object.defineProperty(testWindow, '__testIdleCallbacks', { value: idleCallbacks });
        Object.defineProperty(window, 'requestIdleCallback', {
            value: (callback) => {
                idleCallbacks.push(callback);

                return idleCallbacks.length;
            },
        });
    });

    await page.goto('/');

    const scene = page.locator('[data-architecture-scene]');

    await expect(scene).toHaveAttribute('data-architecture-state', 'idle');
    await expect(scene.locator('[data-architecture-fallback]')).toBeVisible();

    await expect.poll(() => page.evaluate(() => {
        const testWindow = window as Window & { __testIdleCallbacks: IdleRequestCallback[] };

        return testWindow.__testIdleCallbacks.length;
    })).toBeGreaterThan(0);

    await page.evaluate(() => {
        const testWindow = window as Window & { __testIdleCallbacks: IdleRequestCallback[] };
        const callback = testWindow.__testIdleCallbacks.shift();

        callback?.({ didTimeout: false, timeRemaining: () => 50 });
    });

    await expect(scene).toHaveAttribute('data-architecture-state', 'ready');
    await expect(scene.locator('canvas')).toBeVisible();
});

test('testimonial submission exposes labeled fields and supporting copy', async ({ page }) => {
    await page.goto('/testimonials/submit');

    await expect(page.getByLabel('Name')).toBeVisible();
    await expect(page.getByLabel('Role')).toBeVisible();
    await expect(page.getByLabel('Company')).toBeVisible();
    await expect(page.getByLabel('Testimonial')).toBeVisible();
    await expect(page.getByText('Your testimonial will be reviewed before it appears publicly.')).toBeVisible();
});

test('about card can be flipped with the keyboard', async ({ page }) => {
    await page.goto('/about');

    const card = page.getByRole('button', { name: 'Flip Jeffrey Davidson developer card' });

    await expect(card).toHaveAttribute('aria-pressed', 'false');
    await card.focus();
    await page.keyboard.press('Enter');
    await expect(card).toHaveAttribute('aria-pressed', 'true');
});

test('Alpine loads only on pages that use it', async ({ page }) => {
    await page.goto('/');

    await expect.poll(() => page.evaluate(() => typeof window.Alpine)).toBe('undefined');

    await page.goto('/about');

    await expect.poll(() => page.evaluate(() => typeof window.Alpine)).toBe('undefined');

    await page.goto('/blog');

    await expect.poll(() => page.evaluate(() => typeof window.Alpine)).toBe('object');
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
    await page.goto('/blog/how-i-structure-every-laravel-project');

    const copyButton = page.getByRole('button', { name: 'Copy code' }).first();

    await copyButton.focus();
    await expect(copyButton).toBeVisible();
});

test('blog syntax highlighting waits until the browser is idle', async ({ page }) => {
    await page.addInitScript(() => {
        const idleCallbacks: IdleRequestCallback[] = [];
        const testWindow = window as Window & { __testIdleCallbacks: IdleRequestCallback[] };

        Object.defineProperty(testWindow, '__testIdleCallbacks', { value: idleCallbacks });
        Object.defineProperty(window, 'requestIdleCallback', {
            value: (callback) => {
                idleCallbacks.push(callback);

                return idleCallbacks.length;
            },
        });
    });

    await page.goto('/blog/how-i-structure-every-laravel-project');

    await expect(page.locator('html')).toHaveAttribute('data-code-highlighting-state', 'idle');
    await expect(page.locator('.prose code .token')).toHaveCount(0);

    await page.evaluate(() => {
        const testWindow = window as Window & { __testIdleCallbacks: IdleRequestCallback[] };
        const callback = testWindow.__testIdleCallbacks.shift();

        callback?.({ didTimeout: false, timeRemaining: () => 50 });
    });

    await expect(page.locator('html')).toHaveAttribute('data-code-highlighting-state', 'ready');
    await expect(page.locator('.prose code .token').first()).toBeAttached();
});

test('an administrator can reach the dashboard', async ({ page }) => {
    test.skip(!process.env.E2E_ADMIN_EMAIL || !process.env.E2E_ADMIN_PASSWORD);

    await page.emulateMedia({ colorScheme: 'dark' });

    await page.goto('/admin/login');
    await page.locator('input[type="email"]').fill(process.env.E2E_ADMIN_EMAIL!);
    await page.locator('input[type="password"]').fill(process.env.E2E_ADMIN_PASSWORD!);
    await page.locator('button[type="submit"]').click();

    await expect(page).toHaveURL(/\/admin\/?$/);
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Posts', exact: true })).toBeVisible();

    await assertNoHighImpactAccessibilityViolations(page);
});
