import AxeBuilder from '@axe-core/playwright';
import { expect, Page, test } from '@playwright/test';

const publicRoutes = ['/', '/about', '/blog', '/podcasts', '/projects', '/uses'];

async function assertNoCriticalAccessibilityViolations(page: Page): Promise<void> {
    const results = await new AxeBuilder({ page }).analyze();
    const criticalViolations = results.violations.filter((violation) => violation.impact === 'critical');
    const seriousViolations = results.violations.filter((violation) => violation.impact === 'serious');

    if (seriousViolations.length > 0) {
        console.warn(`Accessibility warning: ${seriousViolations.length} serious violation group(s) detected.`);
    }

    expect(criticalViolations).toEqual([]);
}

for (const route of publicRoutes) {
    test(`${route} loads successfully without critical accessibility violations`, async ({ page }) => {
        const response = await page.goto(route);

        expect(response?.ok()).toBeTruthy();
        await expect(page.locator('body')).toBeVisible();
        await assertNoCriticalAccessibilityViolations(page);
    });
}

test('admin login loads with labeled credentials without critical accessibility violations', async ({ page }) => {
    const response = await page.goto('/admin/login');

    expect(response?.ok()).toBeTruthy();
    await expect(page.locator('input[type="email"]')).toHaveAccessibleName(/email/i);
    await expect(page.locator('input[type="password"]')).toHaveAccessibleName(/password/i);

    await assertNoCriticalAccessibilityViolations(page);
});

test('an administrator can reach the dashboard', async ({ page }) => {
    test.skip(!process.env.E2E_ADMIN_EMAIL || !process.env.E2E_ADMIN_PASSWORD);

    await page.goto('/admin/login');
    await page.locator('input[type="email"]').fill(process.env.E2E_ADMIN_EMAIL!);
    await page.locator('input[type="password"]').fill(process.env.E2E_ADMIN_PASSWORD!);
    await page.locator('button[type="submit"]').click();

    await expect(page).toHaveURL(/\/admin\/?$/);
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Posts' })).toBeVisible();

    await assertNoCriticalAccessibilityViolations(page);
});
