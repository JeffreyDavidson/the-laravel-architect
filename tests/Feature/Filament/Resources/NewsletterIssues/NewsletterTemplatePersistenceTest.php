<?php

use App\Enums\NewsletterTemplate;
use App\Models\NewsletterIssue;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('saves the chosen newsletter template and display introduction through the editor', function () {
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateNewsletterIssue::class)
        ->fillForm([
            'title' => 'A useful test',
            'slug' => 'a-useful-test',
            'content' => 'Prove what changed.',
            'email_template' => NewsletterTemplate::PracticalTip,
            'email_deck' => 'Start with the promise.',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(NewsletterIssue::query()->sole())
        ->email_template->toBe(NewsletterTemplate::PracticalTip)
        ->email_deck->toBe('Start with the promise.');
});

it('keeps existing writing when the selected template changes', function () {
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateNewsletterIssue::class)
        ->fillForm(['content' => 'My existing writing.', 'email_template' => NewsletterTemplate::RelatedReading])
        ->assertSchemaStateSet(['content' => 'My existing writing.'], 'form');
});

it('inserts the chosen starter only through the explicit action', function () {
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateNewsletterIssue::class)
        ->fillForm(['content' => 'My existing writing.', 'email_template' => NewsletterTemplate::PracticalTip])
        ->mountAction(TestAction::make('useStarterContent')->schemaComponent('newsletterTemplate', 'form'))
        ->assertSchemaStateSet(['content' => 'My existing writing.'], 'form')
        ->assertActionMounted(TestAction::make('useStarterContent')->schemaComponent('newsletterTemplate', 'form'))
        ->callMountedAction()
        ->assertSchemaStateSet(['content' => file_get_contents(resource_path('newsletter/practical_tip.md'))], 'form');
});

it('rejects unsafe article destinations before saving an issue', function () {
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateNewsletterIssue::class)
        ->fillForm([
            'title' => 'Article', 'slug' => 'article', 'content' => 'A real article.',
            'email_template' => NewsletterTemplate::NewArticle,
            'email_options' => ['article_url' => 'javascript:alert(1)'],
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['email_options.article_url']);

    expect(NewsletterIssue::query()->exists())->toBeFalse();
});

it('requires a description for an uploaded article image', function () {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateNewsletterIssue::class)
        ->fillForm([
            'title' => 'Article', 'slug' => 'article', 'content' => 'A real article.',
            'email_template' => NewsletterTemplate::NewArticle,
            'email_options' => ['hero_path' => UploadedFile::fake()->image('cover.png')],
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['email_options.hero_alt']);
});

it('saves unsaved edits before opening the real email preview', function () {
    actingAs(User::factory()->create(['is_admin' => true]));
    $issue = NewsletterIssue::factory()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['content' => 'The preview should show this.', 'email_template' => NewsletterTemplate::RelatedReading])
        ->callAction('previewEmail')
        ->assertHasNoFormErrors()
        ->assertRedirect(route('preview.newsletterEmail', $issue));

    expect($issue->refresh())
        ->content->toBe('The preview should show this.')
        ->email_template->toBe(NewsletterTemplate::RelatedReading)
        ->sent_at->toBeNull();
});

it('stores a validated article image with its description', function () {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateNewsletterIssue::class)
        ->fillForm([
            'title' => 'Article', 'slug' => 'article', 'content' => 'A real article.',
            'email_template' => NewsletterTemplate::NewArticle,
            'email_options' => [
                'hero_path' => UploadedFile::fake()->image('cover.png'),
                'hero_alt' => 'A concrete illustration.',
            ],
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $issue = NewsletterIssue::query()
        ->sole();
    $options = $issue->getAttribute('email_options');

    if (! is_array($options)) {
        throw new UnexpectedValueException('The saved image settings must be an array.');
    }

    expect($options['hero_path'])->toStartWith('newsletter/');
});

it('keeps the issue own existing image when editing without reuploading it', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()
        ->image('existing.png');
    Storage::disk('public')
        ->put('newsletter/existing.png', $image->getContent());
    actingAs(User::factory()->create(['is_admin' => true]));
    $issue = NewsletterIssue::factory()->create([
        'email_template' => NewsletterTemplate::NewArticle,
        'email_options' => ['hero_path' => 'newsletter/existing.png', 'hero_alt' => 'An existing image.'],
    ]);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['title' => 'Updated article'])
        ->call('save')
        ->assertHasNoFormErrors();

    $options = $issue->refresh()
        ->getAttribute('email_options');

    if (! is_array($options)) {
        throw new UnexpectedValueException('The saved image settings must be an array.');
    }

    expect($options['hero_path'])->toBe('newsletter/existing.png');
});
