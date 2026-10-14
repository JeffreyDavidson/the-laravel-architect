<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

use function Pest\Laravel\withVite;

it('renders page-specific SEO metadata', function () {
    $response = get(route('about'));

    $response
        ->assertOk()
        ->assertSeeHtml('<title>About — Jeffrey Davidson</title>')
        ->assertSeeHtml('<meta name="description" content="Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.">');
});

it('keeps the developer card name out of the about page heading outline', function () {
    $response = get(route('about'));

    $response->assertOk()
        ->assertSeeText('Jeffrey Davidson');

    $content = responseContent($response->getContent());
    $document = new DOMDocument;
    $document->loadHTML($content, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);
    $cards = $xpath->query('//*[@data-about-card]');
    $cardHeadings = $xpath->query('//*[@data-about-card]//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');

    if (! $cards instanceof DOMNodeList || ! $cardHeadings instanceof DOMNodeList) {
        throw new RuntimeException('The markup query failed.');
    }
    expect($cards->length)->toBe(1);
    $card = $cards->item(0);

    if (! $card instanceof DOMElement) {
        throw new RuntimeException('The developer card was not an element.');
    }

    expect($card->textContent)->toContain('Jeffrey Davidson')
        ->and($cardHeadings->length)
        ->toBe(0)
        ->and(stringPosition($content, '<h1'))
        ->toBeLessThan(stringPosition($content, '<h2'));
});

it('describes the developer card stat sheet to assistive technology', function () {
    $response = get(route('about'));

    $response
        ->assertOk()
        ->assertSeeHtml('aria-describedby="about-card-stats"')
        ->assertSeeHtml('id="about-card-stats"');
});

it('renders canonical profile metadata with Jeffrey as its main entity', function () {
    $routeName = 'about';
    $type = 'ProfilePage';
    $name = 'About';
    $url = route($routeName);
    $response = get($url);

    $response
        ->assertOk();

    $content = $response->getContent();

    $structuredData = decodeStructuredData($content);
    $graph = structuredDataGraph($structuredData['@graph'] ?? null);
    $page = structuredDataObject(collect($graph)->firstWhere('@type', $type));

    expect($page)->toMatchArray([
        '@id' => $url.'#page',
        'name' => $name,
        'url' => $url,
        'isPartOf' => [
            '@type' => 'WebSite',
            '@id' => route('home').'#website',
        ],
    ])
        ->and($page['mainEntity'])
        ->toMatchArray([
            '@type' => 'Person',
            '@id' => route('about').'#person',
        ]);
});

it('keeps public technology and channel details consistent on about', function () {
    $response = get(route('about'));
    $response->assertOk()
        ->assertSeeHtml('aria-label="Flip Jeffrey Davidson developer card"')
        ->assertSeeHtml('aria-pressed="false"')
        ->assertSeeHtml('x-data="siteHeader"')
        ->assertSee(configuredString(config('public-site.technology.laravel')))
        ->assertSeeHtml('>8.5</span>')
        ->assertSee('I share practical Laravel videos');
});

it('loads public interactivity and typography from the local Vite bundle on about', function () {
    withVite();
    $manifest = assetManifest();
    $response = get(route('about'));
    $response->assertOk()
        ->assertSeeText('Jeffrey Davidson')
        ->assertDontSeeHtml('font-empera')
        ->assertSeeHtml($manifest['resources/css/app.css']['file'])
        ->assertSeeHtml($manifest['resources/images/avatar-320.webp']['file'])
        ->assertSeeHtml($manifest['resources/images/avatar-640.webp']['file'])
        ->assertSeeHtml('sizes="(min-width: 1024px) 300px, 250px"');
});
