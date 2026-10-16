<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

it('opens the PDF attachment form from the post editor', function (): void {
    // Arrange
    withVite();
    $post = Post::factory()->create(['content' => 'Original article.']);
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = visit(PostResource::getUrl('edit', ['record' => $post], isAbsolute: false))->wait(0);

    // Act
    $page->page()
        ->getByRole('button', ['name' => 'Attach PDF'])
        ->click();

    // Assert
    $page->assertSee('Attach a PDF')
        ->assertPresent('input[type="file"][accept="application/pdf"]')
        ->assertSee('PDF files up to 10 MB.')
        ->assertNoJavaScriptErrors();
});

it('returns to the unchanged post editor when the PDF attachment form is cancelled', function (): void {
    // Arrange
    withVite();
    $post = Post::factory()->create(['content' => 'Original article.']);
    actingAs(User::factory()->create(['is_admin' => true]));
    $page = visit(PostResource::getUrl('edit', ['record' => $post], isAbsolute: false))->wait(0);
    $page->page()
        ->getByRole('button', ['name' => 'Attach PDF'])
        ->click();
    $page->assertSee('Attach a PDF');

    // Act
    $page->page()
        ->getByRole('dialog')
        ->getByRole('button', ['name' => 'Cancel', 'exact' => true])
        ->click();

    // Assert
    $page->assertDontSee('Attach a PDF')
        ->assertScript('document.querySelector(".CodeMirror").CodeMirror.getValue() === "Original article."')
        ->assertNoJavaScriptErrors();
});
