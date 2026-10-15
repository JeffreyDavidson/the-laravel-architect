<?php

use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('serves the newsletter RSS feed with published issues only', function () {
    NewsletterIssue::factory()->published()
        ->create([
            'title' => 'Laravel & PHP <Patterns>',
            'excerpt' => 'Safe & useful <summary>',
        ]);
    NewsletterIssue::factory()->create([
        'title' => 'Draft newsletter issue',
        'published_at' => now()->subDay(),
    ]);
    NewsletterIssue::factory()->published()
        ->create([
            'title' => 'Future newsletter issue',
            'published_at' => now()->addDay(),
        ]);

    $response = get(route('newsletter.rss'));

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSeeHtml('<atom:link href="'.route('newsletter.rss').'" rel="self" type="application/rss+xml" />')
        ->assertSeeHtml('<title>Laravel &amp; PHP &lt;Patterns&gt;</title>')
        ->assertSeeHtml('<description>Safe &amp; useful &lt;summary&gt;</description>')
        ->assertDontSeeHtml('Draft newsletter issue')
        ->assertDontSeeHtml('Future newsletter issue');
});
