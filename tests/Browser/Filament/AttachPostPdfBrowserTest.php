<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;

use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

it('opens the PDF attachment form and returns to the unchanged editor on cancel', function (): void {
    withVite();
    User::factory()->create([
        'email' => 'pdf-editor@example.test',
        'password' => 'browser-test-password',
        'is_admin' => true,
    ]);
    $post = Post::factory()->create(['content' => 'Original article.']);

    $page = visit('/admin/login')->wait(0);
    $page->fill('input[type="email"]', 'pdf-editor@example.test')
        ->fill('input[type="password"]', 'browser-test-password')
        ->click('button[type="submit"]')
        ->assertPathIs('/admin');
    $page->navigate(PostResource::getUrl('edit', ['record' => $post], isAbsolute: false))
        ->click('Attach PDF')
        ->assertSee('Attach a PDF');
    $page->assertPresent('input[type="file"][accept="application/pdf"]')
        ->assertSee('PDF files up to 10 MB.')
        ->screenshot(fullPage: false, filename: 'post-pdf-upload-modal');
    $page->page()
        ->getByRole('dialog')
        ->getByRole('button', ['name' => 'Cancel', 'exact' => true])
        ->click();
    $page->assertScript('document.querySelector(".CodeMirror").CodeMirror.getValue() === "Original article."')
        ->assertNoJavaScriptErrors();
});
