import { expect, type Page } from '@playwright/test';

export class MainNavigation {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async openMobile(): Promise<void> {
        const menu = this.page.locator('#mobile-menu');

        await this.page.getByRole('button', { name: 'Toggle menu' }).click();
        await expect(menu).toBeVisible();
    }

    async navigateTo(label: string): Promise<void> {
        await this.page.getByRole('link', { name: label, exact: true }).click();
    }
}
