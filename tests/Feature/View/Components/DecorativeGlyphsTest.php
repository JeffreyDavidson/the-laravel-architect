<?php

it('hides decorative glyphs from assistive technology', function (string $template, string $pattern) {
    $html = (string) $this->blade($template);

    expect($html)
        ->toMatch($pattern);
})->with([
    'terminal prompt cursor' => [
        '<x-terminal-prompt command="migrate" />',
        '/aria-hidden="true"[^>]*>▊/u',
    ],
    'service card cursor' => [
        '<x-home.service-card number="01" command="make:plan" title="Plan" description="Planning." href="/services" cta="Learn more" />',
        '/aria-hidden="true"[^>]*>▊/u',
    ],
    'section icon' => [
        '<x-public.section-icon>&gt;</x-public.section-icon>',
        '/^<div[^>]*aria-hidden="true"/',
    ],
]);
