<?php

use App\Enums\NewsletterTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\NewsletterIssueResource;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

it('updates the article fields when an editor selects a different template in the browser', function (string $device) {
    withVite();
    actingAs(User::factory()->create(['is_admin' => true, 'name' => 'Newsletter Editor']));
    $devices = visit(NewsletterIssueResource::getUrl('create'))
        ->on();
    $page = $device === 'mobile'
        ? $devices->mobile()
            ->wait(0)
        : $devices->desktop()
            ->wait(0);

    $page->assertSee('Email layout')
        ->assertSee('Article link and image')
        ->assertNoJavaScriptErrors();

    $reviewDirectory = getenv('NEWSLETTER_REVIEW_DIR');

    if (is_string($reviewDirectory) && $reviewDirectory !== '') {
        $filename = "newsletter-editor-{$device}";
        $page->page()
            ->screenshot(true, $filename);
        copy(base_path("tests/Browser/Screenshots/{$filename}.png"), "{$reviewDirectory}/checks/editor-{$device}.png");
    }

    $page->select('Template', NewsletterTemplate::PracticalTip->value)
        ->assertDontSee('Article link and image')
        ->assertSee('Display introduction')
        ->assertNoJavaScriptErrors();
})->with(['desktop', 'mobile']);
