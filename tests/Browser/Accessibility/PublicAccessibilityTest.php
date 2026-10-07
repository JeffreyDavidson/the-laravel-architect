<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Browser\Components\NewsletterForm;
use Tests\Browser\Pages\BlogPostPage;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\PodcastEpisodePage;
use Tests\Browser\Pages\ProjectIndexPage;

use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/channels*' => Http::response([
            'items' => [
                ['statistics' => ['subscriberCount' => 1234]],
            ],
        ]),
    ]);
});

it('provides a keyboard entry point and a programmatic newsletter label', function () {
    $page = HomePage::visit();

    $page->assertAttribute('a[href="#main-content"]', 'href', '#main-content')
        ->assertAttribute('#main-content', 'tabindex', '-1')
        ->assertPresent('label[for="newsletter-email"]')
        ->assertAttribute('#newsletter-email', 'autocomplete', 'email')
        ->assertScript('document.querySelector("#newsletter-email").labels.length', 1)
        ->assertNoJavaScriptErrors();
});

it('keeps newsletter validation accessible and preserves the submitted email', function () {
    $page = HomePage::visit();

    $page->script('document.querySelector("#newsletter-email").form.noValidate = true');
    new NewsletterForm($page)->submit('not-an-email');

    $page->assertPresent('#newsletter-email-error')
        ->assertValue('#newsletter-email', 'not-an-email')
        ->assertAttribute('#newsletter-email', 'aria-invalid', 'true')
        ->assertAttribute('#newsletter-email', 'aria-describedby', 'newsletter-email-error newsletter-privacy');
});

it('gives project entries a heading and a labeled technology list', function () {
    Project::factory()
        ->published()
        ->featured()
        ->create([
            'title' => 'Architecture Decisions',
            'tech_stack' => ['Laravel', 'Pest'],
        ]);

    $page = ProjectIndexPage::visit();

    $page->assertCount('[data-project-entry]', 1)
        ->assertSeeIn('[data-project-entry] h3', 'Architecture Decisions')
        ->assertAttribute('[data-project-entry] ul', 'aria-label', 'Technologies used for Architecture Decisions')
        ->assertCount('[data-project-entry] ul > li', 2)
        ->assertNoJavaScriptErrors();
});

it('exposes podcast navigation, dates, and share actions to assistive technology', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'title' => 'Designing Clear Boundaries',
            'transistor_url' => null,
            'transcript' => <<<'MARKDOWN'
## Architecture notes

Clear boundaries make this episode easier to follow.
MARKDOWN,
            'published_at' => '2026-08-20 12:00:00',
        ]);

    withVite();
    $page = PodcastEpisodePage::visit($podcast, $episode);

    $page->assertPresent('nav[aria-label="Breadcrumb"]')
        ->assertAttribute('nav[aria-label="Breadcrumb"] [aria-current="page"]', 'aria-current', 'page')
        ->assertCount('time[datetime="2026-08-20"]', 2)
        ->assertAttribute('a[aria-label="Share Designing Clear Boundaries on X"]', 'aria-label', 'Share Designing Clear Boundaries on X')
        ->assertAttribute('a[aria-label="Share Designing Clear Boundaries on LinkedIn"]', 'aria-label', 'Share Designing Clear Boundaries on LinkedIn')
        ->assertScript('Array.from(document.querySelectorAll("a.share-btn svg")).every((icon) => icon.getAttribute("aria-hidden") === "true")')
        ->assertPresent('[data-transcript-search]')
        ->assertScript('document.querySelector("[data-transcript-tools]").hidden === false')
        ->assertPresent('[data-transcript-anchor]')
        ->assertScript('document.querySelector("[data-transcript-content] h2, [data-transcript-content] h3, [data-transcript-content] h4").id.startsWith("transcript-")')
        ->click('Read transcript')
        ->fill('Search transcript', 'boundaries')
        ->assertScript('document.querySelector("[data-transcript-status]").textContent === "1 match found"')
        ->assertScript('document.querySelectorAll("[data-transcript-match]").length === 1')
        ->assertNoJavaScriptErrors();
});

/** @return array{0: Podcast, 1: Episode} */
function transcriptEpisode(): array
{
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()
        ->for($podcast)
        ->published()
        ->create([
            'transistor_url' => null,
            'transcript' => "## Opening thoughts\n\nBoundaries matter.\n\n## Boundaries in practice\n\nMore about boundaries here.",
        ]);

    return [$podcast, $episode];
}

it('clears transcript search matches and copies a section link', function () {
    [$podcast, $episode] = transcriptEpisode();
    withVite();
    $page = PodcastEpisodePage::visit($podcast, $episode);

    $page->assertPresent('[data-transcript-anchor]')
        ->click('Read transcript')
        ->fill('Search transcript', 'boundaries')
        ->assertScript('document.querySelector("[data-transcript-status]").textContent === "3 matches found"')
        ->assertScript('document.querySelectorAll("[data-transcript-match]").length === 3')
        ->fill('Search transcript', '')
        ->assertScript('document.querySelector("[data-transcript-status]").textContent === "Search the transcript"')
        ->assertScript('document.querySelectorAll("[data-transcript-match]").length === 0');

    $page->page()
        ->locator('[data-transcript-anchor]')
        ->first()
        ->click();

    $page->assertScript('document.querySelector("[data-transcript-anchor]").getAttribute("aria-label") === "Section link copied"')
        ->assertNoJavaScriptErrors();
});

it('opens the transcript at a deep-linked section', function () {
    [$podcast, $episode] = transcriptEpisode();
    withVite();

    $page = $this->browserPage(route('podcast.episode', [$podcast, $episode]).'#transcript-boundaries-in-practice', 'desktop');

    $page->assertScript('document.querySelector("[data-transcript]").open === true')
        ->assertNoJavaScriptErrors();
});

it('publishes machine-readable dates for articles', function () {
    $post = Post::factory()
        ->published()
        ->create(['published_at' => '2026-08-19 09:00:00']);

    $page = BlogPostPage::visit($post);

    $page->assertAttribute('time[datetime="2026-08-19"]', 'datetime', '2026-08-19')
        ->assertNoJavaScriptErrors();
});

/**
 * JavaScript that resolves to true when the element found by the given
 * expression has at least WCAG AA contrast against its effective background,
 * blending any translucent backgrounds over the nearest opaque one.
 */
function meetsTextContrast(string $findElement): string
{
    return <<<JS
        (() => {
            const element = {$findElement};
            if (!element) { return false; }
            const canvas = document.createElement('canvas').getContext('2d', { willReadFrequently: true });
            const rgba = (color) => { canvas.clearRect(0, 0, 1, 1); canvas.fillStyle = '#000'; canvas.fillStyle = color; canvas.fillRect(0, 0, 1, 1); const d = canvas.getImageData(0, 0, 1, 1).data; return [d[0], d[1], d[2], d[3] / 255]; };
            const luminance = ([r, g, b]) => [r, g, b].map((v) => { v /= 255; return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4; }).reduce((sum, v, i) => sum + v * [0.2126, 0.7152, 0.0722][i], 0);
            let node = element, background = null;
            const tints = [];
            while (node && !background) { const color = rgba(getComputedStyle(node).backgroundColor); if (color[3] > 0.9) { background = color; } else if (color[3] > 0) { tints.unshift(color); } node = node.parentElement; }
            const blended = tints.reduce((base, [r, g, b, a]) => [base[0] * (1 - a) + r * a, base[1] * (1 - a) + g * a, base[2] * (1 - a) + b * a], background ?? rgba(getComputedStyle(document.documentElement).backgroundColor));
            const text = luminance(rgba(getComputedStyle(element).color));
            const surface = luminance(blended);
            return (Math.max(text, surface) + 0.05) / (Math.min(text, surface) + 0.05) >= 4.5;
        })()
        JS;
}

/** JavaScript that holds the newsletter request until `window.releaseNewsletter()` is called. */
function holdNewsletterRequest(): string
{
    return <<<'JS'
        window.fetch = ((send) => (url, options) => url === document.querySelector('[data-newsletter-form]').action
            ? new Promise((resolve) => { window.releaseNewsletter = () => resolve(send(url, options)); })
            : send(url, options))(window.fetch.bind(window))
        JS;
}

it('keeps keyboard focus on the newsletter button while a sign-up is sent', function () {
    withVite();
    $page = $this->browserPage('/', 'desktop');
    $page->assertScript("document.querySelector('[data-newsletter-form]').dataset.ready === 'true'")
        ->script(holdNewsletterRequest());
    $page->page()
        ->locator('#newsletter-email')
        ->fill('reader@example.com');

    $page->page()
        ->locator('[data-newsletter-form] button[type="submit"]')
        ->press('Enter');

    $page->assertScript("document.activeElement === document.querySelector('[data-newsletter-form] button[type=\"submit\"]')")
        ->assertAttribute('[data-newsletter-form]', 'aria-busy', 'true')
        ->assertAttribute('[data-newsletter-form] button[type="submit"]', 'aria-disabled', 'true')
        ->assertScript("document.querySelector('[data-newsletter-form] button[type=\"submit\"]').disabled", false)
        ->script('window.releaseNewsletter()');
    $page->assertSee('Check your email to confirm your subscription.')
        ->assertScript("document.activeElement === document.querySelector('[data-newsletter-form] button[type=\"submit\"]')")
        ->assertAttributeMissing('[data-newsletter-form]', 'aria-busy')
        ->assertAttributeMissing('[data-newsletter-form] button[type="submit"]', 'aria-disabled')
        ->assertNoJavaScriptErrors();
});

it('moves focus to the newsletter email field and announces a repeated error again', function () {
    withVite();
    $page = $this->browserPage('/', 'desktop');
    $page->assertScript("document.querySelector('[data-newsletter-form]').dataset.ready === 'true'");
    $page->page()
        ->locator('#newsletter-email')
        ->fill('bad..address@example.com');
    $page->page()
        ->locator('[data-newsletter-form] button[type="submit"]')
        ->press('Enter');
    $page->assertSee('The email field must be a valid email address.')
        ->script(holdNewsletterRequest());

    $page->page()
        ->locator('[data-newsletter-form] button[type="submit"]')
        ->press('Enter');

    $page->assertScript("document.querySelector('[data-newsletter-feedback]').innerText.trim() === ''")
        ->assertScript("document.querySelectorAll('[data-newsletter-feedback] [role], [data-newsletter-feedback] [aria-live]').length === 0")
        ->script('window.releaseNewsletter()');
    $page->assertSee('The email field must be a valid email address.')
        ->assertScript("document.activeElement === document.querySelector('#newsletter-email')")
        ->assertNoJavaScriptErrors();
});

it('keeps the newsletter success message readable', function (string $theme) {
    withVite();
    $page = $this->browserPageWithTheme('/', 'desktop', $theme);
    $page->assertScript("document.querySelector('[data-newsletter-form]').dataset.ready === 'true'");
    $page->page()
        ->locator('#newsletter-email')
        ->fill('reader@example.com');

    $page->page()
        ->locator('[data-newsletter-form] button[type="submit"]')
        ->click();

    $page->assertSee('Check your email to confirm your subscription.')
        ->assertScript(meetsTextContrast('[...document.querySelectorAll("[data-newsletter-feedback] > div")].find((banner) => !banner.hidden)'))
        ->assertNoJavaScriptErrors();
})->with(['light', 'dark']);

it('keeps search highlights readable in dark mode', function () {
    withVite();
    Post::factory()
        ->published()
        ->create(['title' => 'Laravel Boundaries']);

    $page = $this->browserPageWithTheme('/search?q=Laravel', 'desktop', 'dark');

    $page->assertScript(meetsTextContrast('document.querySelector("main mark")'));
});

it('keeps the developer card badge readable in light mode', function () {
    withVite();

    $page = $this->browserPageWithTheme('/about', 'desktop', 'light');

    $page->assertScript(meetsTextContrast('[...document.querySelectorAll("span")].find((span) => span.textContent.trim() === "Legendary")'));
});

it('wraps the site navigation in a banner landmark', function () {
    withVite();

    $page = $this->browserPageWithTheme('/', 'desktop', 'light');

    $page->assertScript('document.querySelector("body > header nav") !== null')
        ->assertScript('getComputedStyle(document.querySelector("body > header")).position === "sticky"');
});

it('gives footer links comfortable touch targets on phones', function () {
    withVite();

    $page = $this->browserPageWithTheme('/', 'mobile', 'light');

    $page->assertScript('[...document.querySelectorAll("footer ul a")].every((link) => link.getBoundingClientRect().height >= 44)');
});

it('keeps the podcast coming soon badge readable in dark mode', function () {
    withVite();
    $podcast = Podcast::factory()->create();

    $page = $this->browserPageWithTheme(route('podcast.show', $podcast, false), 'desktop', 'dark');

    $page->assertScript(meetsTextContrast('[...document.querySelectorAll("span")].find((span) => span.textContent.trim() === "Coming Soon")'))
        ->assertNoJavaScriptErrors();
});
