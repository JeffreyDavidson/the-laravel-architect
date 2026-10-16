<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    $this->artisanCommand('content:import-public', [
        'path' => base_path('tests/Browser/fixtures/public-content.json'),
    ])->assertSuccessful();
});

it('keeps public routes within the mobile viewport', function (string $route): void {
    // Arrange
    withVite();

    // Act
    $page = $this->browserPageWithTheme($route, 'mobile', 'dark');

    // Assert
    $page->assertScript('document.documentElement.scrollWidth <= document.documentElement.clientWidth')
        ->assertNoJavaScriptErrors();
})->with([
    '/',
    '/about',
    '/services',
    '/blog',
    '/blog/e2e-code-example',
    '/contact',
    '/newsletter/confirmed',
    '/podcasts',
    '/privacy',
    '/projects',
    '/projects/e2e-project',
    '/uses',
]);

it('opens the mobile navigation from the menu button', function (): void {
    // Arrange
    withVite();
    $page = $this->waitForSiteHeader($this->browserPageWithTheme('/', 'mobile', 'dark'));
    $page->assertAttribute('#mobile-menu-btn', 'aria-expanded', 'false')
        ->assertScript('document.querySelector("#mobile-menu").hidden');

    // Act
    $page->page()
        ->getByRole('button', ['name' => 'Toggle menu'])
        ->click();

    // Assert
    $page->assertAttribute('#mobile-menu-btn', 'aria-expanded', 'true')
        ->assertVisible('#mobile-menu')
        ->assertNoJavaScriptErrors();
});

it('closes the mobile navigation with Escape', function (): void {
    // Arrange
    withVite();
    $page = $this->waitForSiteHeader($this->browserPageWithTheme('/', 'mobile', 'dark'));
    $menuButton = $page->page()
        ->getByRole('button', ['name' => 'Toggle menu']);
    $menuButton->click();
    $page->assertAttribute('#mobile-menu-btn', 'aria-expanded', 'true');

    // Act
    $menuButton->press('Escape');

    // Assert
    $page->assertAttribute('#mobile-menu-btn', 'aria-expanded', 'false')
        ->assertScript('document.querySelector("#mobile-menu").hidden')
        ->assertNoJavaScriptErrors();
});

it('navigates to the blog from the mobile navigation', function (): void {
    // Arrange
    withVite();
    $page = $this->waitForSiteHeader($this->browserPageWithTheme('/', 'mobile', 'dark'));
    $page->page()
        ->getByRole('button', ['name' => 'Toggle menu'])
        ->click();
    $page->assertVisible('#mobile-menu');

    // Act
    $page->page()
        ->locator('#mobile-menu')
        ->getByRole('link', ['name' => 'Writing', 'exact' => true])
        ->click();

    // Assert
    $page->assertPathIs('/blog')
        ->assertSee('Notes from the work.')
        ->assertNoJavaScriptErrors();
});

it('persists the mobile theme choice across navigation', function (): void {
    // Arrange
    withVite();
    $page = $this->waitForSiteHeader($this->browserPageWithTheme('/', 'mobile', 'light'));
    $page->page()
        ->getByRole('button', ['name' => 'Toggle menu'])
        ->click();
    $page->assertVisible('#mobile-menu')
        ->assertScript('document.documentElement.classList.contains("dark")', false)
        ->assertAttribute('#mobile-menu [data-theme-toggle]', 'aria-pressed', 'false');

    // Act
    $page->page()
        ->locator('#mobile-menu [data-theme-toggle]')
        ->click();
    $page->assertAttribute('#mobile-menu [data-theme-toggle]', 'aria-pressed', 'true')
        ->assertScript('localStorage.getItem("theme")', 'dark');
    $page->page()
        ->reload();

    // Assert
    $page->assertScript('document.documentElement.classList.contains("dark")')
        ->assertNoJavaScriptErrors();
});
