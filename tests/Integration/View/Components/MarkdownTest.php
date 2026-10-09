<?php

use Illuminate\Support\Facades\Blade;

it('trims spaces inside angle-bracket link destinations', function () {
    $html = Blade::render('<x-markdown :content="$content" />', ['content' => "[Post](< /blog/z >)\n\n![Pic](< /storage/pic.png >)"]);

    expect($html)
        ->toContain('href="/blog/z"', 'src="/storage/pic.png"')
        ->not->toContain('%20');
});
