<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\PodcastEpisodePage;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    $this->artisanCommand('content:import-public', [
        'path' => base_path('tests/Browser/fixtures/public-content.json'),
    ])->assertSuccessful();
});

it('loads public routes without high impact accessibility issues in both themes', function (string $route, string $theme): void {
    $this->withVite();

    $page = $this->browserPageWithTheme($route, 'desktop', $theme);

    // Audit settled content, independently of scroll-reveal animation timing.
    $page->script('document.querySelectorAll("[data-reveal]").forEach(element => { element.style.transition = "none"; element.dataset.reveal = "visible"; });');

    expect(
        $page->page()
            ->locator('body')
            ->count()
    )->toBeGreaterThan(0);

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
    $this->withVite();

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
    $this->withVite();

    $page = HomePage::visit();

    $page->page()
        ->locator('[data-reveal]')
        ->first()
        ->scrollIntoViewIfNeeded();

    $page->assertScript('document.querySelector("[data-reveal]").dataset.reveal === "visible"')
        ->assertNoJavaScriptErrors();
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

it('loads standalone Alpine without Livewire on non-Livewire pages', function (): void {
    $this->withVite();

    foreach (['/', '/about'] as $route) {
        $page = $this->browserPage($route, 'desktop');

        $page->assertScript("typeof window.Alpine === 'object'")
            ->assertScript("typeof window.Livewire === 'undefined'")
            ->assertScript("!performance.getEntriesByType('resource').some(entry => entry.name.includes('/livewire-'))")
            ->assertNoJavaScriptErrors();
    }
});

it('supports blog search and reset with Livewire', function (): void {
    $this->withVite();

    $page = $this->browserPage('/blog', 'desktop');

    $page->assertScript("typeof window.Livewire === 'object' && typeof window.Alpine === 'object'")
        ->assertScript("!performance.getEntriesByType('resource').some(entry => entry.name.includes('/alpine-'))")
        ->click('#theme-toggle')
        ->assertNoJavaScriptErrors();

    $page->page()
        ->locator('#blog-search')
        ->fill('searchable post');
    $page->page()
        ->locator('#blog-search')
        ->press('Enter');

    $page
        ->assertPathIs('/blog')
        ->assertSee('E2E Searchable Post')
        ->assertDontSee('E2E Welcome Post')
        ->assertTitle('Search results — Jeffrey Davidson')
        ->assertScript("JSON.parse(document.querySelector('script[type=\"application/ld+json\"]').textContent)['@graph'].find(item => item['@type'] === 'ItemList').numberOfItems === 1")
        ->click('[data-blog-clear]')
        ->assertSee('E2E Welcome Post')
        ->assertTitle('Blog — Jeffrey Davidson')
        ->assertNoJavaScriptErrors();
});

it('supports search filters and preserves the selected result type', function (): void {
    $this->withVite();

    $page = $this->browserPage('/search', 'desktop');

    $page->page()
        ->locator('#site-search')
        ->fill('E2E');
    $page->page()
        ->locator('#search-type')
        ->selectOption('projects');
    $page->page()
        ->getByRole('button', ['name' => 'Search', 'exact' => true])
        ->click();

    $page
        ->assertPathIs('/search')
        ->assertSee('E2E Project')
        ->assertDontSeeIn('h2', 'Writing');

    expect(
        $page->page()
            ->locator('mark')
            ->textContent()
    )->toContain('E2E');
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

it('reports clipboard failure without claiming the code was copied', function (): void {
    $this->withVite();
    $page = $this->browserPage(route('blog.show', 'e2e-code-example'), 'desktop');
    $page->script('Object.defineProperty(navigator, "clipboard", { configurable: true, value: { writeText: async () => { throw new Error("Denied"); } } }); document.execCommand = () => false;');

    $page->click('.copy-btn')
        ->assertAttribute('.copy-btn', 'aria-label', 'Copy failed')
        ->assertNoJavaScriptErrors();
});

it('keeps audio controls synchronized with the media element', function (): void {
    $this->withVite();
    $podcast = Podcast::query()->where('slug', 'e2e-podcast')
        ->sole();
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Audio controls',
        'description' => 'A deterministic media control test.',
        'audio_url' => 'https://example.test/audio.mp3',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $page = PodcastEpisodePage::visit($podcast, $episode);
    $page->assertScript('document.querySelector("[data-audio]").controls === false')
        ->assertNoJavaScriptErrors();
    $page->script(<<<'JS'
        const audio = document.querySelector('[data-audio]');
        Object.defineProperties(audio, {
            duration: { configurable: true, value: 120 },
            currentTime: { configurable: true, writable: true, value: 30 },
            paused: { configurable: true, writable: true, value: true },
            ended: { configurable: true, writable: true, value: false },
        });
        audio.play = async () => { audio.paused = false; audio.dispatchEvent(new Event('play')); };
        audio.pause = () => { audio.paused = true; audio.dispatchEvent(new Event('pause')); };
        audio.dispatchEvent(new Event('loadedmetadata'));
        JS);

    $page->assertSeeIn('[data-audio-current-time]', '0:30')
        ->assertSeeIn('[data-audio-duration]', '2:00')
        ->click('[data-audio-play]')
        ->assertAttribute('[data-audio-play]', 'aria-label', 'Pause episode')
        ->assertAttribute('[data-audio-player]', 'data-playing', 'true')
        ->click('[data-audio-play]')
        ->assertAttribute('[data-audio-player]', 'data-playing', 'false')
        ->click('[data-audio-skip-back]')
        ->assertSeeIn('[data-audio-current-time]', '0:15')
        ->click('[data-audio-skip-forward]')
        ->assertSeeIn('[data-audio-current-time]', '0:45')
        ->click('[data-audio-speed]')
        ->assertSeeIn('[data-audio-speed-label]', '1.25x')
        ->assertScript('document.querySelector("[data-audio]").playbackRate === 1.25');

    $page->script('const seek = document.querySelector("[data-audio-seek]"); seek.value = 75; seek.dispatchEvent(new Event("input", { bubbles: true }));');
    $page->assertAttribute('[data-audio-seek]', 'aria-valuetext', '1:30 of 2:00')
        ->assertScript('document.querySelector("[data-audio-progress]").style.width === "75%"');
    $page->script('const audio = document.querySelector("[data-audio]"); audio.ended = true; audio.dispatchEvent(new Event("ended")); audio.play = async () => { throw new Error("Playback denied"); }; void 0;');
    $page->click('[data-audio-play]')
        ->assertAttribute('[data-audio-play]', 'aria-label', 'Play episode')
        ->assertAttribute('[data-audio-player]', 'data-playing', 'false')
        ->assertNoJavaScriptErrors();
});

it('loads the podcast video only on activation and copies its share link', function (): void {
    $this->withVite();
    $podcast = Podcast::query()->where('slug', 'e2e-podcast')
        ->sole();
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Video controls',
        'description' => 'A click-to-load video.',
        'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $page = PodcastEpisodePage::visit($podcast, $episode);
    $page->script('const frame = document.querySelector("[data-youtube-player]").content.querySelector("iframe"); frame.removeAttribute("src"); frame.srcdoc = "<p>Video fixture</p>"; Object.defineProperty(navigator, "clipboard", { configurable: true, value: { writeText: async text => { window.copiedEpisodeUrl = text; } } });');

    $page->assertNotPresent('[data-youtube-facade] iframe')
        ->assertEnabled('[data-youtube-play]')
        ->click('[data-youtube-play]')
        ->assertCount('[data-youtube-facade] iframe', 1)
        ->assertScript('document.querySelector("[data-youtube-play]").hidden')
        ->click('[data-podcast-copy-url]')
        ->assertAttribute('[data-podcast-copy-url]', 'aria-label', 'Episode link copied')
        ->assertScript('window.copiedEpisodeUrl === document.querySelector("[data-podcast-copy-url]").dataset.podcastCopyUrl')
        ->assertNoJavaScriptErrors();
});

it('builds styled article navigation from the Blade template', function (): void {
    $this->withVite();
    $post = Post::query()->where('slug', 'e2e-code-example')
        ->sole();
    $post->update(['content' => "## First section\n\nIntroduction.\n\n## Second section\n\nDetails."]);

    $page = $this->browserPage(route('blog.show', $post), 'desktop');

    $page->assertCount('[data-article-toc-link]', 4)
        ->assertAttribute('aside [data-article-toc-link="first-section"]', 'href', '#first-section')
        ->assertScript("getComputedStyle(document.querySelector('[data-article-toc-link]')).borderLeftWidth === '1px'")
        ->assertNoJavaScriptErrors();
});

it('allows an administrator to reach the dashboard', function (string $theme, string $device): void {
    $this->withVite();

    User::factory()->create([
        'name' => 'E2E Admin',
        'email' => 'e2e-admin@example.test',
        'password' => 'e2e-password',
        'is_admin' => true,
    ]);

    $page = $this->browserPageWithTheme('/admin/login', $device, $theme);

    foreach (['dark', 'light', 'system'] as $appearance) {
        $page->click(".tla-auth-theme-switcher button[aria-label='Enable {$appearance} theme']")
            ->assertScript("localStorage.getItem('theme') === '{$appearance}'")
            ->assertScript("document.documentElement.classList.contains('dark') === ('{$appearance}' === 'dark' || ('{$appearance}' === 'system' && matchMedia('(prefers-color-scheme: dark)').matches))");
    }

    $page->page()
        ->locator('input[type="email"]')
        ->fill('e2e-admin@example.test');
    $page->page()
        ->locator('input[type="password"]')
        ->fill('e2e-password');
    $page->page()
        ->locator('button[type="submit"]')
        ->click();

    $page
        ->assertPathIs('/admin')
        ->assertSee('Dashboard')
        ->assertPresent('nav a[href*="posts"]')
        ->assertScript('document.querySelector(".tla-dashboard-hero").scrollWidth <= document.querySelector(".tla-dashboard-hero").clientWidth')
        ->assertScript('getComputedStyle(document.querySelector(".fi-main-ctn")).opacity === "1"')
        ->assertNoAccessibilityIssues(1);

    $page->click('.fi-user-menu-trigger')
        ->assertSee('Sign out')
        ->assertScript(<<<'JS'
            (() => {
                const menu = [...document.querySelectorAll('.fi-dropdown-panel')].find(element => element.textContent.includes('Sign out') && element.getBoundingClientRect().height > 0);
                if (!menu) return false;
                const box = menu.getBoundingClientRect();
                return [0.25, 0.5, 0.75].every(fraction => menu.contains(document.elementFromPoint(box.x + box.width / 2, box.y + box.height * fraction)));
            })()
            JS);
    $page->click(".fi-dropdown-panel button[aria-label='Enable dark theme']")
        ->assertScript("document.documentElement.classList.contains('dark')");
    $page->click('.fi-user-menu-trigger')
        ->click(".fi-dropdown-panel button[aria-label='Enable {$theme} theme']")
        ->click('.fi-user-menu-trigger')
        ->click('.fi-dropdown-panel a[href$="/admin/profile"]')
        ->assertPathIs('/admin/profile')
        ->assertPresent('.fi-sidebar')
        ->assertPresent('.fi-user-menu-trigger');

    if ($device === 'desktop') {
        $page->click('.fi-sidebar-item-btn[href$="/admin"]');
    } else {
        $adminUrl = str_replace('/admin/profile', '/admin', $page->url());
        $page->page()
            ->goto($adminUrl);
    }

    $page->assertPathIs('/admin');

    if ($device === 'desktop') {
        $page->click('.fi-topbar-close-collapse-sidebar-btn')
            ->assertScript(<<<'JS'
                (() => {
                    const button = document.querySelector('.tla-sidebar-primary');
                    const icon = button.querySelector('.tla-sidebar-primary__icon');
                    const buttonBox = button.getBoundingClientRect();
                    const iconBox = icon.getBoundingClientRect();
                    const centered = Math.abs(buttonBox.x + buttonBox.width / 2 - iconBox.x - iconBox.width / 2) < 1 && Math.abs(buttonBox.y + buttonBox.height / 2 - iconBox.y - iconBox.height / 2) < 1;
                    return !document.querySelector('.fi-sidebar').classList.contains('fi-sidebar-open') && centered && buttonBox.width < 60 && getComputedStyle(button.querySelector('.tla-sidebar-primary__label')).display === 'none';
                })()
                JS);
        $page->click('.tla-sidebar-primary');
    } else {
        $page->click('Write post');
    }

    $page
        ->assertPathIs('/admin/posts/create')
        ->assertPresent('.CodeMirror')
        ->assertScript('getComputedStyle(document.querySelector(".fi-main-ctn")).opacity === "1"')
        ->assertNoAccessibilityIssues(1)
        ->assertNoJavaScriptErrors();
})->with(['light', 'dark'])
    ->with(['desktop', 'mobile']);
