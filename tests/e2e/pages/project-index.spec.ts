import { expect, test } from '@playwright/test';
import { ProjectIndexPage } from './ProjectIndexPage';
import { assertNoHighImpactAccessibilityViolations, assertNoHorizontalOverflow } from '../support/accessibility';

for (const width of [390, 768, 1280]) {
    for (const colorScheme of ['light', 'dark'] as const) {
        test(`project index at ${width}px in ${colorScheme} mode`, async ({ page }, testInfo) => {
            const projectIndexPage = new ProjectIndexPage(page);

            await page.setViewportSize({ width, height: 900 });
            await page.emulateMedia({ colorScheme, reducedMotion: 'reduce' });
            await projectIndexPage.goto();
            await projectIndexPage.expectLoaded();
            await expect(page.locator('main a[href*="github.com"]')).toHaveCount(0);
            await assertNoHorizontalOverflow(page);
            await assertNoHighImpactAccessibilityViolations(page);
            await page.screenshot({
                path: testInfo.outputPath('project-index.png'),
                fullPage: true,
            });
            await projectIndexPage.openProject('Ringside');
            await expect(page).toHaveURL(/\/projects\/ringside$/);
        });
    }
}
