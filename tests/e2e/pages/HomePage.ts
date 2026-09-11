import { expect, type Page } from '@playwright/test';

export class HomePage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async goto(): Promise<void> {
        await this.page.goto('/');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page.getByRole('heading', { name: 'Away from the editor' })).toBeVisible();
    }
}
