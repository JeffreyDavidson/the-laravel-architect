<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    $this->artisanCommand('content:import-public', [
        'path' => base_path('tests/Browser/fixtures/public-content.json'),
    ])->assertSuccessful();
});

it('keeps public routes within the mobile viewport', function (string $route): void {
    $this->withVite();

    $page = $this->browserPageWithTheme($route, 'mobile', 'dark');

    $dimensions = $page->page()->evaluate('() => ({
        clientWidth: document.documentElement.clientWidth,
        scrollWidth: document.documentElement.scrollWidth,
    })');

    if (! is_array($dimensions)
        || ! isset($dimensions['clientWidth'], $dimensions['scrollWidth'])
        || ! is_int($dimensions['clientWidth'])
        || ! is_int($dimensions['scrollWidth'])) {
        throw new RuntimeException('The browser viewport dimensions were not returned.');
    }

    expect($dimensions['scrollWidth'])->toBeLessThanOrEqual($dimensions['clientWidth'])
        ->and($page->page()->locator('body')->isVisible())->toBeTrue();
})->with([
    '/',
    '/about',
    '/services',
    '/blog',
    '/blog/e2e-code-example',
    '/contact',
    '/podcasts',
    '/privacy',
    '/projects',
    '/projects/e2e-project',
    '/uses',
]);

it('opens mobile navigation and navigates to the blog', function (): void {
    $this->withVite();

    $page = $this->browserPageWithTheme('/', 'mobile', 'dark');
    $menuButton = $page->page()->getByRole('button', ['name' => 'Toggle menu']);

    expect($page->page()->locator('#mobile-menu')->isHidden())->toBeTrue()
        ->and($menuButton->getAttribute('aria-expanded'))->toBe('false');

    $menuButton->click();

    expect($page->page()->locator('#mobile-menu')->isVisible())->toBeTrue()
        ->and($menuButton->getAttribute('aria-expanded'))->toBe('true');

    $menuButton->press('Escape');
    $page->assertAttribute('#mobile-menu-btn', 'aria-expanded', 'false')
        ->assertScript('document.querySelector("#mobile-menu").hidden');
    $menuButton->click();

    $page->page()->locator('#mobile-menu')->getByRole('link', ['name' => 'Writing', 'exact' => true])->click();

    $page->assertPathIs('/blog')
        ->assertSee('Notes from the work.')
        ->assertNoJavaScriptErrors();
});

it('persists the mobile theme choice across navigation', function (): void {
    $this->withVite();

    $page = $this->browserPageWithTheme('/', 'mobile', 'light');
    $root = $page->page()->locator('html');
    $themeToggle = $page->page()->locator('.theme-toggle-mobile');
    $page->page()->getByRole('button', ['name' => 'Toggle menu'])->click();

    expect($root->getAttribute('class'))->not->toContain('dark')
        ->and($themeToggle->getAttribute('aria-pressed'))->toBe('false');

    $themeToggle->click();

    expect($themeToggle->getAttribute('aria-pressed'))->toBe('true')
        ->and($page->page()->evaluate('localStorage.getItem("theme")'))->toBe('dark');

    $page->page()->reload();

    expect($page->page()->locator('html')->getAttribute('class'))->toContain('dark');
    $page->assertNoJavaScriptErrors();
});
