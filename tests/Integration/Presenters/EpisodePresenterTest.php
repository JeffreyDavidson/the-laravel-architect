<?php

use App\Models\Episode;
use App\Presenters\EpisodePresenter;

it('formats an episode duration stored in seconds', function (?int $seconds, string $expected) {
    $episode = new Episode(['duration_seconds' => $seconds]);

    expect(EpisodePresenter::from($episode)->duration())
        ->toBe($expected);
})->with([
    'not set' => [null, ''],
    'zero' => [0, ''],
    'under a minute rounds up to one minute' => [20, '1 min'],
    'rounds down to the nearest minute' => [89, '1 min'],
    'rounds up to the nearest minute' => [90, '2 min'],
    'whole minutes' => [1500, '25 min'],
    'just under an hour rounds to the hour' => [3599, '1h 0m'],
    'hours and minutes' => [3900, '1h 5m'],
    'two hours' => [7260, '2h 1m'],
]);
