import { devices, expect, test } from '@playwright/test';
import { MainNavigation } from '../components/MainNavigation';
import { ThemeToggle } from '../components/ThemeToggle';
import { assertNoHighImpactAccessibilityViolations, assertNoHorizontalOverflow } from '../support/accessibility';

const publicRoutes = [
    '/',
    '/about',
    '/services',
    '/blog',
    '/blog/how-i-structure-every-laravel-project',
    '/contact',
    '/podcasts',
    '/privacy',
    '/projects',
    '/projects/ringside',
    '/uses',
];

test.use({ ...devices['Pixel 5'] });

test.beforeEach(async ({ page }) => {
    await page.emulateMedia({ colorScheme: 'dark', reducedMotion: 'reduce' });
});

for (const route of publicRoutes) {
    test(`${route} fits the mobile viewport`, async ({ page }) => {
        const response = await page.goto(route);

        expect(response?.ok()).toBeTruthy();
        await expect(page.locator('body')).toBeVisible();
        await assertNoHorizontalOverflow(page);
    });
}

test('mobile navigation opens and navigates to the blog', async ({ page }) => {
    await page.goto('/');

    const navigation = new MainNavigation(page);
    const menuButton = page.getByRole('button', { name: 'Toggle menu' });
    const menu = page.locator('#mobile-menu');

    await expect(menu).toBeHidden();
    await expect(menuButton).toHaveAttribute('aria-expanded', 'false');

    await navigation.openMobile();

    await expect(menu).toBeVisible();
    await expect(menuButton).toHaveAttribute('aria-expanded', 'true');
    await assertNoHighImpactAccessibilityViolations(page);

    await menu.getByRole('link', { name: 'Writing', exact: true }).click();

    await expect(page).toHaveURL(/\/blog$/);
    await expect(
        page.getByRole('heading', {
            name: 'Notes from the work.',
            exact: true,
        }),
    ).toBeVisible();
});

test('mobile theme choice persists across navigation', async ({ page }) => {
    await page.emulateMedia({ colorScheme: 'light' });
    await page.goto('/');

    const root = page.locator('html');
    const themeToggle = new ThemeToggle(page);

    await expect(root).not.toHaveClass(/dark/);
    await page.getByRole('button', { name: 'Toggle menu' }).click();
    await themeToggle.toggleMobile();
    await expect.poll(() => page.evaluate(() => localStorage.getItem('theme'))).toBe('dark');

    await page.reload();

    await expect(root).toHaveClass(/dark/);
});

test('an administrator can use the mobile login and navigation', async ({ page }) => {
    test.skip(!process.env.E2E_ADMIN_EMAIL || !process.env.E2E_ADMIN_PASSWORD);

    await page.goto('/admin/login');
    await assertNoHorizontalOverflow(page);
    await assertNoHighImpactAccessibilityViolations(page);

    await page.locator('input[type="email"]').fill(process.env.E2E_ADMIN_EMAIL!);
    await page.locator('input[type="password"]').fill(process.env.E2E_ADMIN_PASSWORD!);
    await page.locator('button[type="submit"]').click();

    await expect(page).toHaveURL(/\/admin\/?$/);
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await assertNoHorizontalOverflow(page);

    const sidebar = page.locator('#fi-main-sidebar');
    const sidebarButton = page.locator('button[aria-controls="fi-main-sidebar"]').first();

    await expect(sidebar).not.toHaveClass(/fi-sidebar-open/);
    await sidebarButton.click();

    await expect(sidebar).toHaveClass(/fi-sidebar-open/);
    await expect(sidebar.getByRole('link', { name: 'Posts', exact: true })).toBeVisible();
    await assertNoHighImpactAccessibilityViolations(page);
});
