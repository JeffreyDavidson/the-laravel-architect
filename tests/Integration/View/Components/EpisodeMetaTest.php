<?php

use App\Models\Episode;
use Illuminate\Support\Facades\Date;

it('shows the episode code, date and duration', function () {
    $episode = new Episode([
        'season_number' => 1,
        'episode_number' => 4,
        'duration_seconds' => 1500,
        'published_at' => Date::parse('2026-06-14 12:00:00'),
    ]);

    $html = (string) $this->blade(
        '<x-podcast.episode-meta :episode="$episode" code-class="font-mono" separator-class="divider" item-class="meta" />',
        ['episode' => $episode],
    );

    expect($html)->toContain('<span class="font-mono">S01E04</span>')
        ->toContain('class="meta">Jun 14, 2026</time>')
        ->toMatch('/<span\s+class="meta"\s*>25 min<\/span>/')
        ->and(substr_count($html, '<span class="divider">·</span>'))
        ->toBe(2);
});

it('leaves out the code and the duration when they are not wanted or missing', function () {
    $episode = new Episode([
        'season_number' => 1,
        'episode_number' => 4,
        'published_at' => Date::parse('2026-06-14 12:00:00'),
    ]);

    $html = (string) $this->blade(
        '<x-podcast.episode-meta :episode="$episode" separator-class="divider" />',
        ['episode' => $episode],
    );

    expect($html)->toContain('<time datetime="2026-06-14">Jun 14, 2026</time>')
        ->not->toContain('S01E04')
        ->not->toContain('divider');
});
