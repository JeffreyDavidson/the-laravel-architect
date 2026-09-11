<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('renders responsive project images without overflow', function (string $route, string $device) {
    $this->withVite();
    Storage::fake('public', ['url' => '/test-project-images?path=']);
    Storage::disk('public')->put('showcase.png', UploadedFile::fake()->image('showcase.png', 1280, 720)->getContent());

    // Serve only these isolated fixtures through the browser test application.
    Route::get('/test-project-images', function (Request $request) {
        $path = ltrim((string) $request->query('path'), '/');
        abort_unless(in_array($path, ['showcase.png', 'responsive/showcase-640.webp', 'responsive/showcase-1280.webp'], true), 404);

        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => Storage::disk('public')->mimeType($path),
        ]);
    });

    $project = Project::query()->create([
        'title' => 'Screenshot layout fixture',
        'description' => 'A test project with an uploaded screenshot.',
        'featured_image_path' => 'showcase.png',
        'is_featured' => true,
        'status' => ProjectStatus::Published,
    ]);

    $project->refresh();
    expect($project->featured_image_path)->toBe('showcase.png');

    $page = $this->browserPage(
        route($route, $route === 'projects.show' ? $project : [], absolute: false),
        $device,
    );

    $page->assertScript('document.querySelectorAll("main picture img").length', 1)
        ->assertScript('document.querySelector("main picture img").complete && document.querySelector("main picture img").naturalWidth > 0')
        ->assertScript('document.querySelector("main picture img").currentSrc.includes("/responsive/showcase-")')
        ->assertScript('document.documentElement.scrollWidth <= document.documentElement.clientWidth')
        ->assertScript('Math.abs(document.querySelector("main picture img").getBoundingClientRect().width / document.querySelector("main picture img").getBoundingClientRect().height - 16 / 9) < 0.02')
        ->assertNoJavaScriptErrors();
})->with(['projects.index', 'projects.show'])->with(['mobile', 'desktop']);

it('keeps image-free projects navigable', function (string $device) {
    $this->withVite();

    $project = Project::query()->create([
        'title' => 'A project without a screenshot',
        'description' => 'Practical software built around a client’s needs.',
        'is_featured' => false,
        'status' => ProjectStatus::Published,
    ]);

    $page = $this->browserPage(route('projects.index', absolute: false), $device);

    $page = $page->assertSeeIn('#more-projects-heading', 'More projects')
        ->assertCount('[data-project-entry]', 1)
        ->assertCount('[data-project-entry] img', 0)
        ->assertScript('document.documentElement.scrollWidth <= document.documentElement.clientWidth');

    $page->click('[data-project-entry] h3 a');

    $page->assertSeeIn('#main-content h1', $project->title)
        ->assertCount('[data-project-detail] img', 0)
        ->assertSee('The full project story is coming soon.')
        ->assertScript('document.documentElement.scrollWidth <= document.documentElement.clientWidth')
        ->assertNoJavaScriptErrors();

    $page->click('All projects');

    $page->assertSeeIn('#main-content h1', 'Selected projects');
})->with(['mobile', 'desktop']);

it('offers contact when there are no published projects', function (string $device) {
    $this->withVite();

    Project::query()->create([
        'title' => 'Unpublished client project',
        'description' => 'Not ready for the portfolio.',
        'status' => ProjectStatus::Draft,
    ]);

    $page = $this->browserPage(route('projects.index', absolute: false), $device);

    $page->assertSee('Project details aren’t available here yet.')
        ->assertDontSee('Unpublished client project')
        ->assertCount('[data-project-entry]', 0)
        ->assertAttribute('section[aria-labelledby="projects-contact-heading"] a', 'href', route('contact'))
        ->assertScript('document.documentElement.scrollWidth <= document.documentElement.clientWidth')
        ->assertNoJavaScriptErrors();
})->with(['mobile', 'desktop']);
