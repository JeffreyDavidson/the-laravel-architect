import { expect, test } from '@playwright/test';
import { HomePage } from './HomePage';
import { assertNoHighImpactAccessibilityViolations, assertNoHorizontalOverflow } from '../support/accessibility';

for (const width of [390, 768, 1280]) {
    for (const colorScheme of ['light', 'dark'] as const) {
        test(`homepage at ${width}px in ${colorScheme} mode`, async ({ page }, testInfo) => {
            const homePage = new HomePage(page);

            await page.setViewportSize({ width, height: 900 });
            await page.emulateMedia({ colorScheme, reducedMotion: 'reduce' });
            await homePage.goto();

            await homePage.expectLoaded();
            await assertNoHorizontalOverflow(page);
            await assertNoHighImpactAccessibilityViolations(page);

            await page.locator('.media-section').screenshot({
                path: testInfo.outputPath('homepage-media.png'),
            });
            await page.locator('[data-home-services]').screenshot({
                path: testInfo.outputPath('homepage-services.png'),
                style: 'header { visibility: hidden !important; }',
            });
            await page.locator('[data-home-work]').screenshot({
                path: testInfo.outputPath('homepage-work.png'),
                style: 'header { visibility: hidden !important; }',
            });
        });
    }
}
