<?php

use App\Support\Markdown\TrimLinkDestinationsExtension;
use League\CommonMark\CommonMarkConverter;

covers(TrimLinkDestinationsExtension::class);

function markdownWithTrimmedLinks(string $markdown): string
{
    $converter = new CommonMarkConverter;
    $environment = $converter->getEnvironment();
    $environment->addExtension(new TrimLinkDestinationsExtension);
    $html = $converter->convert($markdown);

    return $html->getContent();
}

it('trims spaces around link and image destinations', function (string $markdown, string $expected) {
    expect(markdownWithTrimmedLinks($markdown))
        ->toContain($expected);
})->with([
    'link in angle brackets' => ['[a](< /blog/z >)', 'href="/blog/z"'],
    'image in angle brackets' => ['![i](< /img/a.png >)', 'src="/img/a.png"'],
    'absolute link' => ['[a](< https://x.test/a >)', 'href="https://x.test/a"'],
]);

it('keeps spaces inside a destination', function () {
    expect(markdownWithTrimmedLinks('[a](</blog/my post>)'))
        ->toContain('href="/blog/my%20post"');
});

it('leaves code untouched', function () {
    expect(markdownWithTrimmedLinks('`[a](< /blog/z >)`'))
        ->toContain('<code>[a](&lt; /blog/z &gt;)</code>');
});
