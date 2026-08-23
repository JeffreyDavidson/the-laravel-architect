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

    for (const name of ['Read the Blog', 'View Projects']) {
        const link = page.getByRole('link', { name, exact: true });

        await expect(link).toBeVisible();
        await expect.poll(async () => {
            const box = await link.boundingBox();

            return box !== null && box.y + box.height <= 720;
        }).toBeTruthy();
    }
});

test('homepage code preview switches between examples', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/');

    await expect(page.locator('#code-routes')).toBeVisible();
    await expect(page.locator('#code-architect')).toBeHidden();

    await page.locator('[data-code-tab="architect"]').click();

    await expect(page.locator('#code-routes')).toBeHidden();
    await expect(page.locator('#code-architect')).toBeVisible();
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

test('blog code blocks expose a keyboard-accessible copy action', async ({ page }) => {
    await page.goto('/blog/how-i-structure-every-laravel-project');

    const copyButton = page.getByRole('button', { name: 'Copy code' }).first();

    await copyButton.focus();
    await expect(copyButton).toBeVisible();
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
