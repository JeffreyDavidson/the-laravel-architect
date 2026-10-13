<?php

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Post;
use App\Models\User;
use App\Presenters\PostPresenter;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\CreatePost;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\EditPost;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('adds a public PDF link without saving or publishing the post until requested', function () {
    Storage::fake('public');
    $post = Post::factory()->create(['content' => 'Original article.']);
    $pdf = UploadedFile::fake()->createWithContent('companion.pdf', "%PDF-1.4\nCompanion slides\n%%EOF");

    $page = livewire(EditPost::class, ['record' => $post->getRouteKey()]);
    $page->callAction(TestAction::make('attachPdf')->schemaComponent('content'), [
        'pdf' => $pdf,
        'label' => 'Download the companion slides (PDF, 8 pages)',
    ]);
    $page->assertHasNoFormErrors();

    $path = Storage::disk('public')->allFiles('attachments/posts')[0];
    $url = Storage::disk('public')->url($path);
    $page->assertSchemaStateSet(['content' => "Original article.\n\n[Download the companion slides (PDF, 8 pages)]({$url})"]);
    expect($path)->toEndWith('.pdf')
        ->not->toContain('companion.pdf')
        ->and(Storage::disk('public')->get($path))
        ->toBe("%PDF-1.4\nCompanion slides\n%%EOF");
    $post->refresh();
    expect($post->content)->toBe('Original article.');

    $page->call('save');
    $page->assertHasNoFormErrors();

    $post->refresh();
    expect($post->content)->toContain("]({$url})")
        ->and($post->isPublished())
        ->toBeFalse();
    get(PostPresenter::from($post)->previewUrl())
        ->assertSeeHtml('href="'.$url.'"')
        ->assertSee('Download the companion slides (PDF, 8 pages)');
});

it('rejects invalid PDF uploads without storing a file or changing content', function (UploadedFile $file) {
    Storage::fake('public');
    $post = Post::factory()->create(['content' => 'Original article.']);

    $page = livewire(EditPost::class, ['record' => $post->getRouteKey()]);
    $page->callAction(TestAction::make('attachPdf')->schemaComponent('content'), [
        'pdf' => $file,
        'label' => 'Download PDF',
    ]);
    $page->assertHasFormErrors(['pdf']);
    $page->assertSchemaStateSet(['content' => 'Original article.'], 'form');

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
    $post->refresh();
    expect($post->content)->toBe('Original article.');
})->with([
    'renamed text' => fn (): UploadedFile => UploadedFile::fake()->createWithContent('fake.pdf', 'This is not a PDF.')
        ->mimeType('text/plain'),
    'image' => fn (): UploadedFile => UploadedFile::fake()->image('photo.png'),
    'oversized PDF' => fn (): UploadedFile => UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf'),
]);

it('rejects a supplied storage path instead of a new upload', function () {
    Storage::fake('public');
    Storage::disk('public')->put('attachments/posts/existing.pdf', '%PDF-1.4');
    $post = Post::factory()->create(['content' => 'Original article.']);

    $page = livewire(EditPost::class, ['record' => $post->getRouteKey()]);
    $page->callAction(TestAction::make('attachPdf')->schemaComponent('content'), [
        'pdf' => ['tampered' => 'attachments/posts/existing.pdf'],
        'label' => 'Download PDF',
    ]);
    $page->assertHasFormErrors(['pdf']);
    $page->assertSchemaStateSet(['content' => 'Original article.'], 'form');

    expect(Storage::disk('public')->allFiles())->toBe(['attachments/posts/existing.pdf']);
});

it('refuses attachments if the administrator loses permission after opening the editor', function () {
    Storage::fake('public');
    $post = Post::factory()->create();
    $page = livewire(EditPost::class, ['record' => $post->getRouteKey()]);
    $page->mountAction(TestAction::make('attachPdf')->schemaComponent('content'));
    $page->fillForm([
        'pdf' => UploadedFile::fake()->create('companion.pdf', 10, 'application/pdf'),
        'label' => 'Download PDF',
    ]);
    actingAs(User::factory()->create());

    $page->call('callMountedAction');
    $page->assertForbidden();

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('escapes link text and preserves existing whitespace', function () {
    Storage::fake('public');
    $post = Post::factory()->create(['content' => '    An indented code example.']);

    $page = livewire(EditPost::class, ['record' => $post->getRouteKey()]);
    $page->callAction(TestAction::make('attachPdf')->schemaComponent('content'), [
        'pdf' => UploadedFile::fake()->create('companion.pdf', 10, 'application/pdf'),
        'label' => '[Slides](https://example.test)',
    ]);
    $page->call('save');
    $page->assertHasNoFormErrors();

    $post->refresh();
    expect($post->content)
        ->toStartWith('    An indented code example.')
        ->toContain('[\\[Slides\\](https://example.test)](');
});

it('supports a PDF in a new post without creating the post automatically', function () {
    Storage::fake('public');

    $page = livewire(CreatePost::class);
    $page->callAction(TestAction::make('attachPdf')->schemaComponent('content'), [
        'pdf' => UploadedFile::fake()->create('companion.pdf', 10, 'application/pdf'),
        'label' => 'Download PDF',
    ]);
    $page->assertHasNoFormErrors();
    $path = Storage::disk('public')->allFiles('attachments/posts')[0];
    $url = Storage::disk('public')->url($path);
    $page->assertSchemaStateSet(['content' => "[Download PDF]({$url})"]);

    expect(Post::query()->count())->toBe(0);
});

it('leaves PDF actions off other content editors', function () {
    livewire(CreateProject::class)
        ->assertActionDoesNotExist(TestAction::make('attachPdf')->schemaComponent('content'));
});
