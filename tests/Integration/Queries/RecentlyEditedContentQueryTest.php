<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Queries\RecentlyEditedContentQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

it('returns the most recently edited content of every type, newest first', function () {
    $records = [
        'Old post' => [Post::factory(), '2026-08-19 09:00:00'],
        'New episode' => [Episode::factory(), '2026-08-19 15:00:00'],
        'Middle issue' => [NewsletterIssue::factory(), '2026-08-19 12:00:00'],
        'Recent project' => [Project::factory(), '2026-08-19 14:00:00'],
    ];

    foreach ($records as $title => [$factory, $updatedAt]) {
        $record = $factory->create(['title' => $title]);
        if (! $record instanceof Model) {
            throw new RuntimeException('The factory must create a model.');
        }

        $record->forceFill(['updated_at' => Date::parse($updatedAt)])
            ->saveQuietly();
    }

    $titles = app(RecentlyEditedContentQuery::class)
        ->get(3)
        ->pluck('title')
        ->all();

    expect($titles)->toBe(['New episode', 'Recent project', 'Middle issue']);
});
