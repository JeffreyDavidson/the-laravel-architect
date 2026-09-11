import { expect, type Page } from '@playwright/test';

export class ProjectDetailPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async goto(slug: string): Promise<void> {
        await this.page.goto(`/projects/${slug}`);
    }

    async expectLoaded(name: string): Promise<void> {
        await expect(this.page.getByRole('heading', { level: 1, name })).toBeVisible();
    }
}
