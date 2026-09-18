<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Browser\Pages\HomePage;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    $this->artisanCommand('content:import-public', [
        'path' => base_path('tests/Browser/fixtures/public-content.json'),
    ])->assertSuccessful();
});

it('loads public routes without high impact accessibility issues in both themes', function (string $route, string $theme): void {
    $this->withVite();

    $page = $this->browserPageWithTheme($route, 'desktop', $theme);

    expect($page->page()->locator('body')->count())->toBeGreaterThan(0);

    $page->assertNoAccessibilityIssues(1)
        ->assertNoJavaScriptErrors();
})->with([
    'home' => '/',
    'about' => '/about',
    'services' => '/services',
    'blog' => '/blog',
    'blog post' => '/blog/e2e-code-example',
    'contact' => '/contact',
    'podcasts' => '/podcasts',
    'privacy' => '/privacy',
    'projects' => '/projects',
    'project' => '/projects/e2e-project',
    'uses' => '/uses',
])->with(['light', 'dark']);

it('keeps the homepage hero actions visible at a laptop viewport height', function (): void {
    $this->withVite();

    $page = HomePage::visit();
    $page->resize(1280, 720);

    foreach (['Discuss a Project', 'View Projects'] as $label) {
        $link = $page->page()->locator('[data-home-hero]')->getByRole('link', ['name' => $label, 'exact' => true]);
        $box = $link->boundingBox();

        if ($box === null) {
            throw new RuntimeException("The {$label} link is not visible.");
        }

        expect($box['y'] + $box['height'])->toBeLessThanOrEqual(720);
    }
});

it('supports the homepage services link', function (): void {
    $this->withVite();

    $page = HomePage::visit();

    $page->assertPresent('[data-home-services]')
        ->assertCount('[data-home-services] dt', 3)
        ->click('Explore services')
        ->assertPathIs('/services');
});

it('supports keyboard interaction on the about card', function (): void {
    $this->withVite();

    $page = $this->browserPage('/about', 'desktop');
    $card = $page->page()->getByRole('button', ['name' => 'Flip Jeffrey Davidson developer card']);

    expect($card->getAttribute('aria-pressed'))->toBe('false');
    $card->focus();
    $card->press('Enter');

    expect($card->getAttribute('aria-pressed'))->toBe('true');
});

it('keeps Alpine off pages without Livewire', function (): void {
    $this->withVite();

    foreach (['/', '/about'] as $route) {
        $page = $this->browserPage($route, 'desktop');

        expect($page->page()->evaluate('typeof window.Alpine'))->toBe('undefined');
    }
});

it('supports blog search and reset with Livewire', function (): void {
    $this->withVite();

    $page = $this->browserPage('/blog', 'desktop');

    $page->page()->locator('#blog-search')->fill('searchable post');
    $page->page()->locator('#blog-search')->press('Enter');

    $page
        ->assertPathIs('/blog')
        ->assertSee('E2E Searchable Post')
        ->assertDontSee('E2E Welcome Post')
        ->click('[data-blog-clear]')
        ->assertSee('E2E Welcome Post');
});

it('supports search filters and preserves the selected result type', function (): void {
    $this->withVite();

    $page = $this->browserPage('/search', 'desktop');

    $page->page()->locator('#site-search')->fill('E2E');
    $page->page()->locator('#search-type')->selectOption('projects');
    $page->page()->getByRole('button', ['name' => 'Search', 'exact' => true])->click();

    $page
        ->assertPathIs('/search')
        ->assertSee('E2E Project')
        ->assertDontSeeIn('h2', 'Writing');

    expect($page->page()->locator('mark')->textContent())->toContain('E2E');
});

it('exposes the code copy action and delayed syntax highlighting', function (): void {
    $this->withVite();

    $page = $this->browserPage('/blog/e2e-code-example', 'desktop');

    $page->assertPresent('.copy-btn')
        ->click('.copy-btn')
        ->assertAttribute('.copy-btn', 'aria-label', 'Copied');

    $page->assertScript("document.documentElement.dataset.codeHighlightingState === 'ready'")
        ->assertPresent('.prose code .token');
});

it('allows an administrator to reach the dashboard', function (): void {
    $this->withVite();

    User::factory()->create([
        'name' => 'E2E Admin',
        'email' => 'e2e-admin@example.test',
        'password' => 'e2e-password',
        'is_admin' => true,
    ]);

    $page = $this->browserPageWithTheme('/admin/login', 'desktop', 'dark');

    $page->page()->locator('input[type="email"]')->fill('e2e-admin@example.test');
    $page->page()->locator('input[type="password"]')->fill('e2e-password');
    $page->page()->locator('button[type="submit"]')->click();

    $page
        ->assertPathIs('/admin')
        ->assertSee('Dashboard')
        ->assertPresent('nav a[href*="posts"]')
        ->assertNoAccessibilityIssues(1);
});
