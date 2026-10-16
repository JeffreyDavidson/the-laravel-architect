<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Pest\Browser\Api\AwaitableWebpage;
use Tests\Browser\Pages\AboutPage;
use Tests\Browser\Pages\BlogIndexPage;
use Tests\Browser\Pages\BlogPostPage;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\PodcastEpisodePage;
use Tests\Browser\Pages\PublicPage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    $this->artisanCommand('content:import-public', [
        'path' => base_path('tests/Browser/fixtures/public-content.json'),
    ])->assertSuccessful();
});

it('loads public routes without high impact accessibility issues in both themes', function (string $route, string $theme): void {
    withVite();

    $page = $this->browserPageWithTheme($route, 'desktop', $theme);

    if ($route === '/') {
        // The home component resets the reveal state when it starts; let it finish before settling the content.
        $page->assertScript('document.querySelector("[data-home-reveal]").dataset.ready === "true"');
    }

    // Audit settled content, independently of scroll-reveal animation timing.
    $page->script('document.querySelectorAll("[data-reveal]").forEach(element => { element.style.transition = "none"; element.dataset.reveal = "visible"; });');

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
    'archive' => '/archive',
    'newsletter' => '/newsletter',
    'search results' => '/search?q=laravel',
])->with(['light', 'dark']);

it('keeps the homepage hero actions visible at a laptop viewport height', function (): void {
    withVite();

    $page = HomePage::visit();
    $page->resize(1280, 720);

    foreach (['Discuss a Project', 'View Projects'] as $label) {
        $link = $page->page()
            ->locator('[data-home-hero]')
            ->getByRole('link', ['name' => $label, 'exact' => true]);
        $box = $link->boundingBox();

        if ($box === null) {
            throw new RuntimeException("The {$label} link is not visible.");
        }

        expect($box['y'] + $box['height'])->toBeLessThanOrEqual(720);
    }
});

it('keeps homepage hero copy readable over the artwork on mobile', function (): void {
    withVite();

    $page = $this->browserPageWithTheme('/', 'mobile', 'dark');
    $hero = $page->page()
        ->locator('[data-home-hero]');
    $contrast = $page->page()
        ->evaluate(<<<'JS'
            async () => {
                const hero = document.querySelector("[data-home-hero]");
                const image = hero.querySelector("picture img");
                const copy = Array.from(hero.querySelectorAll("p")).find((paragraph) =>
                    paragraph.textContent.includes("Architecture, modernization, and hands-on development")
                );

                await Promise.all([image.decode(), document.fonts.ready]);

                const imageCanvas = document.createElement("canvas");
                imageCanvas.width = image.naturalWidth;
                imageCanvas.height = image.naturalHeight;
                const imageContext = imageCanvas.getContext("2d", { willReadFrequently: true });
                imageContext.drawImage(image, 0, 0);
                const imagePixels = imageContext.getImageData(0, 0, imageCanvas.width, imageCanvas.height);

                const overlayCanvas = document.createElement("canvas");
                const overlayContext = overlayCanvas.getContext("2d");
                overlayContext.fillStyle = getComputedStyle(hero, "::after").backgroundColor;
                overlayContext.fillRect(0, 0, 1, 1);
                const overlay = overlayContext.getImageData(0, 0, 1, 1).data;
                const overlayAlpha = overlay[3] / 255;

                const foregroundCanvas = document.createElement("canvas");
                const foregroundContext = foregroundCanvas.getContext("2d");
                foregroundContext.fillStyle = getComputedStyle(copy).color;
                foregroundContext.fillRect(0, 0, 1, 1);
                const foreground = foregroundContext.getImageData(0, 0, 1, 1).data;

                const imageRect = image.getBoundingClientRect();
                const imageStyle = getComputedStyle(image);
                const scale = Math.max(
                    imageRect.width / image.naturalWidth,
                    imageRect.height / image.naturalHeight
                );
                const renderedWidth = image.naturalWidth * scale;
                const renderedHeight = image.naturalHeight * scale;
                const position = imageStyle.objectPosition.split(/\s+/);
                const positionFactor = (value, start, end) => {
                    if (value.endsWith("%")) {
                        return Number.parseFloat(value) / 100;
                    }

                    if (value === start) {
                        return 0;
                    }

                    if (value === end) {
                        return 1;
                    }

                    return 0.5;
                };
                const imageLeft = imageRect.left
                    + (imageRect.width - renderedWidth) * positionFactor(position[0], "left", "right");
                const imageTop = imageRect.top
                    + (imageRect.height - renderedHeight) * positionFactor(position[1] ?? position[0], "top", "bottom");
                const textNode = copy.firstChild;
                const range = document.createRange();
                const luminance = (channel) => {
                    const normalized = channel / 255;
                    return normalized <= 0.04045
                        ? normalized / 12.92
                        : ((normalized + 0.055) / 1.055) ** 2.4;
                };
                let minimumContrastRatio = Number.POSITIVE_INFINITY;
                let sampleCount = 0;

                for (let offset = 0; offset < textNode.length; offset += 1) {
                    if (/\s/.test(textNode.textContent[offset])) {
                        continue;
                    }

                    range.setStart(textNode, offset);
                    range.setEnd(textNode, offset + 1);
                    const character = range.getBoundingClientRect();

                    if (character.width === 0 || character.height === 0) {
                        continue;
                    }

                    const x = Math.floor((character.left + character.width / 2 - imageLeft) / scale);
                    const y = Math.floor((character.top + character.height / 2 - imageTop) / scale);

                    if (x < 0 || y < 0 || x >= image.naturalWidth || y >= image.naturalHeight) {
                        continue;
                    }

                    const imageOffset = (y * image.naturalWidth + x) * 4;
                    const background = [0, 1, 2].map((channel) =>
                        overlay[channel] * overlayAlpha + imagePixels.data[imageOffset + channel] * (1 - overlayAlpha)
                    );
                    const foregroundLuminance = 0.2126 * luminance(foreground[0])
                        + 0.7152 * luminance(foreground[1])
                        + 0.0722 * luminance(foreground[2]);
                    const backgroundLuminance = 0.2126 * luminance(background[0])
                        + 0.7152 * luminance(background[1])
                        + 0.0722 * luminance(background[2]);
                    const brighter = Math.max(foregroundLuminance, backgroundLuminance);
                    const darker = Math.min(foregroundLuminance, backgroundLuminance);

                    minimumContrastRatio = Math.min(minimumContrastRatio, (brighter + 0.05) / (darker + 0.05));
                    sampleCount += 1;
                }

                return {
                    minimumContrastRatio,
                    sampleCount,
                    mobileImage: image.currentSrc.includes("home-hero-mobile-"),
                };
            }
        JS);
    $heading = $hero->locator('h1');
    $copy = $hero->getByText('Architecture, modernization, and hands-on development for teams carrying real production complexity.');
    $headingIsVisible = $heading->isVisible();
    $copyIsVisible = $copy->isVisible();

    if (! is_array($contrast)
        || ! isset($contrast['minimumContrastRatio'], $contrast['sampleCount'], $contrast['mobileImage'])
        || ! is_float($contrast['minimumContrastRatio'])
        || ! is_int($contrast['sampleCount'])
        || ! is_bool($contrast['mobileImage'])) {
        throw new RuntimeException('The mobile hero contrast measurements were not returned.');
    }

    expect($headingIsVisible)
        ->toBeTrue()
        ->and($copyIsVisible)
        ->toBeTrue()
        ->and($contrast['mobileImage'])
        ->toBeTrue()
        ->and($contrast['sampleCount'])
        ->toBeGreaterThan(0)
        ->and($contrast['minimumContrastRatio'])
        ->toBeGreaterThanOrEqual(4.5);

    $page->assertNoJavaScriptErrors();
});

it('initializes homepage reveal animations', function (): void {
    withVite();

    $page = HomePage::visit();
    $page->assertScript('document.querySelector("[data-home-reveal]").dataset.ready === "true"');

    $page->page()
        ->locator('[data-reveal]')
        ->first()
        ->scrollIntoViewIfNeeded();

    $page->assertScript('document.querySelector("[data-reveal]").dataset.reveal === "visible"')
        ->assertNoJavaScriptErrors();
});

it('counts the homepage proof figures up to their targets', function (): void {
    withVite();

    $page = HomePage::visit();
    $page->assertScript('document.querySelector("[data-home-reveal]").dataset.ready === "true"');
    $page->script('window.countUpChanges = 0; new MutationObserver(() => window.countUpChanges++).observe(document.querySelector("[data-count-up]"), { childList: true, characterData: true, subtree: true });');

    $page->page()
        ->locator('[data-count-up]')
        ->first()
        ->scrollIntoViewIfNeeded();

    $page->assertScript('window.countUpChanges > 0')
        ->assertScript('document.querySelector("[data-count-up]").textContent.trim() === document.querySelector("[data-count-up]").dataset.target')
        ->assertNoJavaScriptErrors();
});

it('lists three services on the homepage', function (): void {
    // Arrange
    withVite();

    // Act
    $page = HomePage::visit();

    // Assert
    $page->assertPresent('[data-home-services]')
        ->assertCount('[data-home-services] dt', 3)
        ->assertNoJavaScriptErrors();
});

it('opens the services page from the homepage services link', function (): void {
    // Arrange
    withVite();
    $page = HomePage::visit();

    // Act
    $page->page()
        ->locator('[data-home-services]')
        ->getByRole('link', ['name' => 'Explore services'])
        ->click();

    // Assert
    $page->assertPathIs('/services')
        ->assertNoJavaScriptErrors();
});

it('supports keyboard interaction on the about card', function (): void {
    withVite();

    $page = AboutPage::visit();
    $card = $page->page()
        ->getByRole('button', ['name' => 'Flip Jeffrey Davidson developer card']);

    $page->assertScript('document.querySelector("[data-about-card-surface]").style.transform !== ""')
        ->assertAttribute('[data-about-card]', 'aria-pressed', 'false');
    $card->focus();
    $card->press('Enter');

    $page->assertAttribute('[data-about-card]', 'aria-pressed', 'true');
    $card->press('Space');
    $page->assertAttribute('[data-about-card]', 'aria-pressed', 'false')
        ->assertNoJavaScriptErrors();
});

it('loads standalone Alpine without Livewire on non-Livewire pages', function (string $publicPage): void {
    // Arrange
    /** @var class-string<PublicPage> $publicPage */
    withVite();

    // Act
    $page = $publicPage::visit();

    // Assert
    $page->assertScript("typeof window.Alpine === 'object'")
        ->assertScript("typeof window.Livewire === 'undefined'")
        ->assertScript("!performance.getEntriesByType('resource').some(entry => entry.name.includes('/livewire-'))")
        ->assertNoJavaScriptErrors();
})->with([
    'home' => HomePage::class,
    'about' => AboutPage::class,
]);

it('loads Livewire with its bundled Alpine on the blog', function (): void {
    // Arrange
    withVite();

    // Act
    $page = BlogIndexPage::visit();

    // Assert
    $page->assertScript("typeof window.Livewire === 'object' && typeof window.Alpine === 'object'")
        ->assertScript('window.Livewire.all().length > 0')
        ->assertScript("document.querySelector('[data-blog-filter]').dataset.ready === 'true'")
        ->assertScript("!performance.getEntriesByType('resource').some(entry => entry.name.includes('/alpine-'))")
        ->assertNoJavaScriptErrors();
});

it('toggles the theme on the Livewire blog page', function (): void {
    // Arrange
    withVite();
    $page = $this->waitForSiteHeader(BlogIndexPage::visit());
    $page->assertScript('document.documentElement.classList.contains("dark")', false);

    // Act
    $page->page()
        ->locator('#theme-toggle')
        ->click();

    // Assert
    $page->assertScript('document.documentElement.classList.contains("dark")')
        ->assertNoJavaScriptErrors();
});

it('searches the blog with Livewire', function (): void {
    // Arrange
    withVite();
    $page = BlogIndexPage::visit();
    $page->assertScript("document.querySelector('[data-blog-filter]').dataset.ready === 'true'");

    // Act
    $page->page()
        ->locator('#blog-search')
        ->fill('searchable post');
    $page->page()
        ->locator('#blog-search')
        ->press('Enter');

    // Assert
    $page
        ->assertPathIs('/blog')
        ->assertVisible('[data-blog-clear]')
        ->assertSee('E2E Searchable Post')
        ->assertDontSee('E2E Welcome Post')
        ->assertTitle('Search results — Jeffrey Davidson')
        ->assertScript("document.querySelector('meta[name=\"robots\"]')?.content === 'noindex, follow'")
        ->assertScript("JSON.parse(document.querySelector('script[type=\"application/ld+json\"]').textContent)['@graph'].find(item => item['@type'] === 'ItemList').numberOfItems === 1")
        ->assertNoJavaScriptErrors();
});

it('resets a blog search with Livewire', function (): void {
    // Arrange
    withVite();
    $page = BlogIndexPage::visit();
    $page->assertScript("document.querySelector('[data-blog-filter]').dataset.ready === 'true'");
    $page->page()
        ->locator('#blog-search')
        ->fill('searchable post');
    $page->page()
        ->locator('#blog-search')
        ->press('Enter');
    $page->assertVisible('[data-blog-clear]')
        ->assertDontSee('E2E Welcome Post');

    // Act
    $page->page()
        ->getByRole('link', ['name' => 'Clear search'])
        ->click();

    // Assert
    $page->assertSee('E2E Welcome Post')
        ->assertTitle('Blog — Jeffrey Davidson')
        ->assertScript("document.querySelector('meta[name=\"robots\"]') === null")
        ->assertNoJavaScriptErrors();
});

it('filters the blog by category when a category pill is clicked', function (): void {
    // Arrange
    withVite();
    $page = BlogIndexPage::visit();
    $page->assertScript("document.querySelector('[data-blog-filter]').dataset.ready === 'true'");

    // Act
    $page->page()
        ->getByRole('navigation', ['name' => 'Filter articles by category'])
        ->locator('a[href$="category=e2e-career"]')
        ->click();

    // Assert
    $page->assertAriaAttribute('[aria-label="Filter articles by category"] a[href$="category=e2e-career"]', 'current', 'page')
        ->assertSee('E2E Searchable Post')
        ->assertDontSee('E2E Welcome Post')
        ->assertPathIs('/blog')
        ->assertQueryStringHas('category', 'e2e-career')
        ->assertNoJavaScriptErrors();
});

it('supports search filters and preserves the selected result type', function (): void {
    // Arrange
    withVite();
    $page = $this->browserPage('/search', 'desktop');
    $page->page()
        ->locator('#site-search')
        ->fill('E2E');
    $page->page()
        ->locator('#search-type')
        ->selectOption('projects');

    // Act
    $page->page()
        ->getByRole('button', ['name' => 'Search', 'exact' => true])
        ->click();

    // Assert
    $page
        ->assertPathIs('/search')
        ->assertSee('E2E Project')
        ->assertDontSeeIn('h2', 'Writing')
        ->assertScript('document.querySelector("mark").textContent.includes("E2E")')
        ->assertNoJavaScriptErrors();
});

it('marks code as copied from the copy action', function (): void {
    // Arrange
    withVite();
    $page = BlogPostPage::visit(Post::query()->where('slug', 'e2e-code-example')
        ->sole());
    // Wait for Alpine to bind the button, so the single click below isn't lost on a slow runner.
    $page->assertScript('document.querySelector("[data-code-copy]")._x_dataStack !== undefined');

    // Act
    $page->page()
        ->locator('[data-code-copy]')
        ->first()
        ->click();

    // Assert
    $page->assertAttribute('[data-code-copy]', 'aria-label', 'Copied')
        ->assertNoJavaScriptErrors();
});

it('highlights code syntax after the article loads', function (): void {
    // Arrange
    withVite();

    // Act
    $page = BlogPostPage::visit(Post::query()->where('slug', 'e2e-code-example')
        ->sole());

    // Assert
    $page->assertScript("document.documentElement.dataset.codeHighlightingState === 'ready'")
        ->assertPresent('.prose code .token')
        ->assertNoJavaScriptErrors();
});

it('keeps stray whitespace out of code blocks around the copy action', function (): void {
    // Arrange
    withVite();

    // Act
    $page = BlogPostPage::visit(Post::query()->where('slug', 'e2e-code-example')
        ->sole());

    // Assert
    $page->assertPresent('[data-code-copy]')
        ->assertScript('Array.from(document.querySelectorAll("[data-article-prose] pre")).every((pre) => Array.from(pre.childNodes).every((node) => node.nodeType === Node.ELEMENT_NODE))')
        ->assertScript('Array.from(document.querySelectorAll("[data-article-prose] pre")).every((pre) => pre.getBoundingClientRect().height < pre.querySelector("code").getBoundingClientRect().height + 60)')
        ->assertNoJavaScriptErrors();
});

it('reports clipboard failure without claiming the code was copied', function (): void {
    withVite();
    $page = BlogPostPage::visit(Post::query()->where('slug', 'e2e-code-example')
        ->sole());
    $page->script('Object.defineProperty(navigator, "clipboard", { configurable: true, value: { writeText: async () => { throw new Error("Denied"); } } }); document.execCommand = () => false;');

    $page->click('[data-code-copy]')
        ->assertAttribute('[data-code-copy]', 'aria-label', 'Copy failed')
        ->assertNoJavaScriptErrors();
});

/**
 * A published episode of the imported podcast with a YouTube video and no Transistor player.
 *
 * @return array{0: Podcast, 1: Episode}
 */
function youtubeEpisode(): array
{
    $podcast = Podcast::query()->where('slug', 'e2e-podcast')
        ->sole();
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'transistor_url' => null,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

    return [$podcast, $episode];
}

it('loads the podcast video only on activation', function (): void {
    // Arrange
    withVite();
    [$podcast, $episode] = youtubeEpisode();
    $page = PodcastEpisodePage::visit($podcast, $episode);
    // Keep YouTube out of the test: the player template's iframe gets local content instead of the embed URL.
    $page->script('const frame = document.querySelector("[data-youtube-player]").content.querySelector("iframe"); frame.removeAttribute("src"); frame.srcdoc = "<p>Video fixture</p>";');
    $page->assertNotPresent('[data-youtube-facade] iframe')
        ->assertEnabled('[data-youtube-play]')
        // Wait for Alpine to bind the facade, so the single click below isn't lost on a slow runner.
        ->assertScript('document.querySelector("[data-youtube-facade]")._x_dataStack !== undefined');

    // Act
    $page->page()
        ->locator('[data-youtube-play]')
        ->click();

    // Assert
    $page->assertCount('[data-youtube-facade] iframe', 1)
        ->assertScript('document.querySelector("[data-youtube-play]").hidden')
        ->assertNoJavaScriptErrors();
});

it('copies the podcast episode share link', function (): void {
    // Arrange
    withVite();
    [$podcast, $episode] = youtubeEpisode();
    $page = PodcastEpisodePage::visit($podcast, $episode);
    $page->script('Object.defineProperty(navigator, "clipboard", { configurable: true, value: { writeText: async text => { window.copiedEpisodeUrl = text; } } });');
    // Wait for Alpine to bind the button, so the single click below isn't lost on a slow runner.
    $page->assertScript('document.querySelector("[data-podcast-copy-url]")._x_dataStack !== undefined');

    // Act
    $page->page()
        ->locator('[data-podcast-copy-url]')
        ->click();

    // Assert
    $page->assertAttribute('[data-podcast-copy-url]', 'aria-label', 'Episode link copied')
        ->assertScript('window.copiedEpisodeUrl === document.querySelector("[data-podcast-copy-url]").dataset.podcastCopyUrl')
        ->assertNoJavaScriptErrors();
});

it('builds styled article navigation from the Blade template', function (): void {
    withVite();
    $post = Post::query()->where('slug', 'e2e-code-example')
        ->sole();
    $post->update(['content' => "## First section\n\nIntroduction.\n\n## Second section\n\nDetails."]);

    $page = BlogPostPage::visit($post);

    $page->assertCount('[data-article-toc-link]', 4)
        ->assertAttribute('aside [data-article-toc-link="first-section"]', 'href', '#first-section')
        ->assertScript("getComputedStyle(document.querySelector('[data-article-toc-link]')).borderLeftWidth === '1px'")
        ->assertNoJavaScriptErrors();
});

it('marks the article section being read in the contents list', function (): void {
    withVite();
    $filler = str_repeat("Some paragraph text that makes the section long enough to scroll through.\n\n", 18);
    $post = Post::query()->where('slug', 'e2e-code-example')
        ->sole();
    $post->update(['content' => "## First section\n\n{$filler}## Second section\n\n{$filler}"]);

    $page = BlogPostPage::visit($post);

    $page->assertCount('[data-article-toc-link]', 4)
        ->script('const heading = document.getElementById("second-section"); window.scrollTo(0, heading.getBoundingClientRect().top + window.scrollY - window.innerHeight * 0.25);');

    $page->assertScript('document.querySelector("[data-article-toc-link][aria-current=\"true\"]")?.dataset.articleTocLink === "second-section"')
        ->assertNoJavaScriptErrors();
});

it('switches the admin sign-in page theme', function (string $appearance, string $browserTheme, bool $isDark, string $device): void {
    // Arrange
    withVite();
    $page = $this->browserPageWithTheme('/admin/login', $device, $browserTheme);

    // Act
    $page->page()
        ->getByRole('button', ['name' => "Enable {$appearance} theme"])
        ->click();

    // Assert
    $page->assertScript('localStorage.getItem("theme")', $appearance)
        ->assertScript('document.documentElement.classList.contains("dark")', $isDark)
        ->assertNoJavaScriptErrors();
})->with([
    'dark in a light browser' => ['dark', 'light', true],
    'light in a dark browser' => ['light', 'dark', false],
    'system in a dark browser' => ['system', 'dark', true],
    'system in a light browser' => ['system', 'light', false],
])->with(['desktop', 'mobile']);

it('signs an administrator in to the dashboard', function (string $theme, string $device): void {
    // Arrange
    withVite();
    User::factory()->create([
        'email' => 'e2e-admin@example.test',
        'password' => 'e2e-password',
        'is_admin' => true,
    ]);
    $page = $this->browserPageWithTheme('/admin/login', $device, $theme);
    $page->page()
        ->locator('input[type="email"]')
        ->fill('e2e-admin@example.test');
    $page->page()
        ->locator('input[type="password"]')
        ->fill('e2e-password');

    // Act
    $page->page()
        ->getByRole('button', ['name' => 'Sign in'])
        ->click();

    // Assert
    $page
        ->assertPathIs('/admin')
        ->assertSee('Dashboard')
        ->assertPresent('nav a[href*="posts"]')
        ->assertScript('document.querySelector(".tla-dashboard-hero").scrollWidth <= document.querySelector(".tla-dashboard-hero").clientWidth')
        ->assertScript('getComputedStyle(document.querySelector(".fi-main-ctn")).opacity === "1"')
        ->assertNoAccessibilityIssues(1)
        ->assertNoJavaScriptErrors();
})->with(['light', 'dark'])
    ->with(['desktop', 'mobile']);

/**
 * Opens the admin account menu once through Playwright: Pest retries a chained click, which could close it again.
 *
 * The trigger's aria-expanded is bound by Alpine, so waiting for "false" first also waits for the menu to start.
 */
function openAdminUserMenu(AwaitableWebpage $page): AwaitableWebpage
{
    $page->assertAttribute('button[aria-label="User menu"]', 'aria-expanded', 'false');
    $page->page()
        ->getByRole('button', ['name' => 'User menu'])
        ->click();

    $page->assertAttribute('button[aria-label="User menu"]', 'aria-expanded', 'true');

    return $page;
}

it('shows the open admin account menu above the page content', function (string $device): void {
    // Arrange
    withVite();
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = $this->browserPage('/admin', $device);

    // Act
    openAdminUserMenu($page);

    // Assert
    $page->assertSee('Sign out')
        ->assertScript(<<<'JS'
            (() => {
                const menu = [...document.querySelectorAll('.fi-dropdown-panel')].find(element => element.textContent.includes('Sign out') && element.getBoundingClientRect().height > 0);
                if (!menu) return false;
                const box = menu.getBoundingClientRect();
                return [0.25, 0.5, 0.75].every(fraction => menu.contains(document.elementFromPoint(box.x + box.width / 2, box.y + box.height * fraction)));
            })()
            JS)
        ->assertNoJavaScriptErrors();
})->with(['desktop', 'mobile']);

it('switches the admin theme from the account menu', function (string $appearance, string $browserTheme, bool $isDark, string $device): void {
    // Arrange
    withVite();
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = openAdminUserMenu($this->browserPageWithTheme('/admin', $device, $browserTheme));

    // Act
    $page->page()
        ->getByRole('button', ['name' => "Enable {$appearance} theme"])
        ->click();

    // Assert
    $page->assertScript('localStorage.getItem("theme")', $appearance)
        ->assertScript('document.documentElement.classList.contains("dark")', $isDark)
        ->assertAttribute('button[aria-label="User menu"]', 'aria-expanded', 'false')
        ->assertNoJavaScriptErrors();
})->with([
    'dark in a light browser' => ['dark', 'light', true],
    'light in a dark browser' => ['light', 'dark', false],
])->with(['desktop', 'mobile']);

it('opens the admin profile from the account menu', function (string $device): void {
    // Arrange
    withVite();
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = openAdminUserMenu($this->browserPage('/admin', $device));

    // Act
    $page->page()
        ->locator('a[href$="/admin/profile"]')
        ->click();

    // Assert
    $page->assertPathIs('/admin/profile')
        ->assertPresent('#fi-main-sidebar')
        ->assertPresent('button[aria-label="User menu"]')
        ->assertNoJavaScriptErrors();
})->with(['desktop', 'mobile']);

it('returns to the dashboard from the desktop sidebar', function (): void {
    // Arrange
    withVite();
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = $this->browserPage('/admin/profile', 'desktop');

    // Act
    $page->page()
        ->getByRole('navigation', ['name' => 'Sidebar navigation'])
        ->getByRole('link', ['name' => 'Dashboard', 'exact' => true])
        ->click();

    // Assert
    $page->assertPathIs('/admin')
        ->assertNoJavaScriptErrors();
});

/** JavaScript that is true when the desktop sidebar is collapsed to a centred, icon-only create action. */
function collapsedSidebarCreateAction(): string
{
    return <<<'JS'
        (() => {
            const button = document.querySelector('a[aria-label="New post"]');
            const icon = button.querySelector('.tla-sidebar-primary__icon');
            const buttonBox = button.getBoundingClientRect();
            const iconBox = icon.getBoundingClientRect();
            const centered = Math.abs(buttonBox.x + buttonBox.width / 2 - iconBox.x - iconBox.width / 2) < 1 && Math.abs(buttonBox.y + buttonBox.height / 2 - iconBox.y - iconBox.height / 2) < 1;
            return !document.getElementById('fi-main-sidebar').classList.contains('fi-sidebar-open') && centered && buttonBox.width < 60 && getComputedStyle(button.querySelector('.tla-sidebar-primary__label')).display === 'none';
        })()
        JS;
}

it('collapses the desktop sidebar to a centred create action', function (): void {
    // Arrange
    withVite();
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = $this->browserPage('/admin', 'desktop');

    // Act
    $page->page()
        ->getByRole('button', ['name' => 'Collapse sidebar'])
        ->click();

    // Assert
    $page->assertScript(collapsedSidebarCreateAction())
        ->assertNoJavaScriptErrors();
});

it('opens the post editor from the collapsed desktop sidebar', function (string $theme): void {
    // Arrange
    withVite();
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = $this->browserPageWithTheme('/admin', 'desktop', $theme);
    $page->page()
        ->getByRole('button', ['name' => 'Collapse sidebar'])
        ->click();
    $page->assertScript(collapsedSidebarCreateAction());

    // Act
    $page->page()
        ->getByRole('link', ['name' => 'New post'])
        ->click();

    // Assert
    $page
        ->assertPathIs('/admin/posts/create')
        ->assertPresent('.CodeMirror')
        ->assertScript('getComputedStyle(document.querySelector(".fi-main-ctn")).opacity === "1"')
        ->assertNoAccessibilityIssues(1)
        ->assertNoJavaScriptErrors();
})->with(['light', 'dark']);

it('opens the post editor from the dashboard quick link on mobile', function (string $theme): void {
    // Arrange
    withVite();
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = $this->browserPageWithTheme('/admin', 'mobile', $theme);

    // Act
    $page->page()
        ->getByRole('link', ['name' => 'Write post'])
        ->click();

    // Assert
    $page
        ->assertPathIs('/admin/posts/create')
        ->assertPresent('.CodeMirror')
        ->assertScript('getComputedStyle(document.querySelector(".fi-main-ctn")).opacity === "1"')
        ->assertNoAccessibilityIssues(1)
        ->assertNoJavaScriptErrors();
})->with(['light', 'dark']);

it('subscribes to the newsletter in place without reloading the page', function (): void {
    withVite();

    $page = HomePage::visit();

    $page->assertScript("document.querySelector('[data-newsletter-form]').dataset.ready === 'true'")
        ->script("window.newsletterMarker = 'kept'");
    $page->page()
        ->locator('#newsletter-email')
        ->fill('reader@example.com');
    $page->page()
        ->locator('[data-newsletter-form] button[type="submit"]')
        ->click();

    $page->assertSee('Check your email to confirm your subscription.')
        ->assertScript("window.newsletterMarker === 'kept'")
        ->assertScript("document.querySelector('#newsletter-email').value === ''")
        ->assertPathIs('/')
        ->assertNoJavaScriptErrors();
});

it('shows the newsletter error in place and keeps what was typed', function (): void {
    withVite();

    $page = HomePage::visit();

    $page->assertScript("document.querySelector('[data-newsletter-form]').dataset.ready === 'true'")
        ->script("window.newsletterMarker = 'kept'");
    $page->page()
        ->locator('#newsletter-email')
        ->fill('bad..address@example.com');
    $page->page()
        ->locator('[data-newsletter-form] button[type="submit"]')
        ->click();

    $page->assertSee('The email field must be a valid email address.')
        ->assertScript("window.newsletterMarker === 'kept'")
        ->assertScript("document.querySelector('#newsletter-email').value === 'bad..address@example.com'")
        ->assertScript("document.querySelector('#newsletter-email').getAttribute('aria-invalid') === 'true'")
        ->assertNoJavaScriptErrors();
});

it('keeps the footer at the bottom of a short page', function (string $device): void {
    withVite();

    $page = $this->browserPage(route('newsletter.confirmed'), $device);

    $page->assertSee('You’re confirmed')
        ->assertScript("document.querySelector('footer').getBoundingClientRect().bottom >= window.innerHeight - 1")
        ->assertNoJavaScriptErrors();
})->with(['desktop', 'mobile']);

it('confirms a newsletter subscription from the email link without a click', function (): void {
    withVite();

    $token = 'one-click-token';
    $subscriber = Subscriber::factory()
        ->pending()
        ->create(['verification_token_hash' => hash('sha256', $token)]);
    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );

    $page = $this->browserPage($url, 'desktop');

    $page->assertScript("window.location.pathname === '/newsletter/confirmed'")
        ->assertSee('You’re confirmed')
        ->assertNoJavaScriptErrors();
    $subscriber->refresh();
    expect($subscriber->verified_at)
        ->not
        ->toBeNull();
});

it('shows the confirming state from the first paint and never the confirm prompt', function (): void {
    withVite();

    $token = 'first-paint-token';
    $subscriber = Subscriber::factory()
        ->pending()
        ->create(['verification_token_hash' => hash('sha256', $token)]);
    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );
    // Records every heading and whether a submit button was visible on the confirm page, from the first parsed node until it navigates away.
    $recorder = <<<'SCRIPT'
        if (location.pathname.startsWith('/newsletter/confirm/')) {
            const seen = [];
            const record = () => {
                document.querySelectorAll('h1').forEach(heading => seen.push(heading.textContent.trim()));
                document.querySelectorAll('form button[type="submit"]').forEach(button => {
                    if (button.checkVisibility()) {
                        seen.push('visible submit button');
                    }
                });
                sessionStorage.setItem('confirmPageStates', JSON.stringify([...new Set(seen)]));
            };
            new MutationObserver(record).observe(document, { childList: true, subtree: true, characterData: true, attributes: true });
        }
        SCRIPT;

    $page = $this->browserPage(route('newsletter.confirmed'), 'desktop');
    $page->page()
        ->context()
        ->addInitScript($recorder);
    $page->page()
        ->goto($url);

    $page->assertScript("window.location.pathname === '/newsletter/confirmed'")
        ->assertScript("JSON.parse(sessionStorage.getItem('confirmPageStates')).includes('Confirming your subscription…')")
        ->assertScript("JSON.parse(sessionStorage.getItem('confirmPageStates')).length === 1")
        ->assertSee('You’re confirmed')
        ->assertNoJavaScriptErrors();
    $subscriber->refresh();
    expect($subscriber->verified_at)
        ->not
        ->toBeNull();
});

it('loads Cloudflare Turnstile once when the contact form is used', function (): void {
    // Arrange
    config()->set('services.turnstile.site_key', 'test-site-key');
    withVite();
    // Stands in for Cloudflare's script so the test needs no network: the inserted tag keeps its src but gets a
    // non-JavaScript type, which stops the browser fetching it, and a stub widget API records each render.
    $turnstileStub = <<<'SCRIPT'
        if (location.pathname === '/contact') {
            window.turnstileRenders = [];
            const append = HTMLHeadElement.prototype.append;
            HTMLHeadElement.prototype.append = function (...nodes) {
                nodes.filter(node => node instanceof HTMLScriptElement && node.src.startsWith('https://challenges.cloudflare.com/turnstile/'))
                    .forEach(script => {
                        script.type = 'text/plain';
                        window.turnstile = { render: (widget, options) => window.turnstileRenders.push(options.sitekey) };
                        setTimeout(() => script.dispatchEvent(new Event('load')));
                    });

                return append.apply(this, nodes);
            };
        }
        SCRIPT;
    $page = HomePage::visit();
    $page->page()
        ->context()
        ->addInitScript($turnstileStub);
    $page->page()
        ->goto(route('contact.create'));
    $page->assertScript("document.querySelectorAll('[data-turnstile-widget]').length === 1")
        ->assertScript("document.querySelector('[data-contact-form]').dataset.ready === 'true'");

    // Act
    $page->page()
        ->locator('input[name="name"]')
        ->focus();
    $page->page()
        ->locator('input[name="email"]')
        ->focus();

    // Assert
    $page->assertScript("document.querySelectorAll('script[src*=\"challenges.cloudflare.com/turnstile\"]').length === 1")
        ->assertScript('JSON.stringify(window.turnstileRenders)', '["test-site-key"]')
        ->assertScript("!performance.getEntriesByType('resource').some(entry => entry.name.includes('challenges.cloudflare.com'))")
        ->assertNoJavaScriptErrors();
});
