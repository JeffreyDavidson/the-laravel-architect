import { expect, test } from '@playwright/test';
import { ProjectDetailPage } from './ProjectDetailPage';
import { assertNoHighImpactAccessibilityViolations, assertNoHorizontalOverflow } from '../support/accessibility';

for (const width of [390, 768, 1280]) {
    for (const colorScheme of ['light', 'dark'] as const) {
        test(`project detail at ${width}px in ${colorScheme} mode`, async ({ page }, testInfo) => {
            const projectDetailPage = new ProjectDetailPage(page);

            await page.setViewportSize({ width, height: 900 });
            await page.emulateMedia({ colorScheme, reducedMotion: 'reduce' });
            await projectDetailPage.goto('ringside');
            await projectDetailPage.expectLoaded('Ringside');
            await expect(page.getByRole('link', { name: 'Explore the code' })).toHaveCount(0);
            await expect(page.locator('main a[href*="github.com"]')).toHaveCount(0);
            await assertNoHorizontalOverflow(page);
            await assertNoHighImpactAccessibilityViolations(page);
            await page.screenshot({
                path: testInfo.outputPath('project-detail.png'),
                fullPage: true,
            });
            await page
                .getByRole('link', {
                    name: 'Discuss a similar project',
                    exact: true,
                })
                .click();
            await expect(page).toHaveURL(/\/contact$/);
        });
    }
}
