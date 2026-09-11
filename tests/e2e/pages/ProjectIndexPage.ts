import { expect, type Page } from '@playwright/test';

export class ProjectIndexPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async goto(): Promise<void> {
        await this.page.goto('/projects');
    }

    async expectLoaded(): Promise<void> {
        await expect(
            this.page.getByRole('heading', {
                level: 1,
                name: 'Selected projects',
            }),
        ).toBeVisible();
    }

    async openProject(name: string): Promise<void> {
        await this.page
            .getByRole('link', {
                name: new RegExp(`^Explore the project\\s*:\\s*${name}$`),
            })
            .click();
    }
}
