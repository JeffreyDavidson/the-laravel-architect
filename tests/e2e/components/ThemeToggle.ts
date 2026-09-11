import { expect, type Page } from '@playwright/test';

export class ThemeToggle {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async toggleDesktop(): Promise<void> {
        await this.page.getByRole('button', { name: 'Toggle theme' }).first().click();
        await expect(this.page.locator('html')).toHaveClass(/dark/);
    }

    async toggleMobile(): Promise<void> {
        await this.page.locator('#mobile-menu').getByRole('button', { name: 'Toggle theme' }).click();
        await expect(this.page.locator('html')).toHaveClass(/dark/);
    }
}
