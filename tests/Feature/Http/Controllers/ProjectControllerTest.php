<?php

use App\Enums\PublishStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows published projects once in their featured groups without repository links', function () {
    foreach ([['Later featured', true, 2], ['First featured', true, 1], ['Other work', false, 0]] as [$title, $featured, $order]) {
        Project::query()->create([
            'title' => $title,
            'description' => "Summary for {$title}.",
            'is_featured' => $featured,
            'sort_order' => $order,
            'github_url' => 'https://github.com/example/private-repository',
            'status' => PublishStatus::Published,
        ]);
    }

    Project::query()->create(['title' => 'Unpublished work', 'description' => 'Private draft.', 'status' => PublishStatus::Draft]);

    $response = $this->get(route('projects.index'));

    $response->assertOk()
        ->assertSeeInOrder(['Summary for First featured.', 'Summary for Later featured.', 'Summary for Other work.'])
        ->assertDontSee('Unpublished work')
        ->assertDontSee('private-repository')
        ->assertDontSee('Open source projects')
        ->assertSee('What are you working on?');

    $content = $response->getContent();
    if (! is_string($content)) {
        throw new RuntimeException('Expected project index HTML.');
    }

    expect(substr_count($content, 'data-project-entry'))->toBe(3);
});

it('offers a contact path when no published projects are available', function () {
    $response = $this->get(route('projects.index'));

    $response->assertOk()
        ->assertSee('Project details aren’t available here yet.')
        ->assertSee(route('contact'), false)
        ->assertDontSee('data-project-entry', false)
        ->assertDontSee('featured-projects-heading', false)
        ->assertDontSee('more-projects-heading', false);
});

it('uses responsive uploaded images in either project group', function (bool $featured) {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('showcase.png', 1280, 720);
    Storage::disk('public')->put('projects/showcase.png', $image->getContent());

    $project = Project::query()->create([
        'title' => 'Project showcase',
        'description' => 'An uploaded product screenshot.',
        'featured_image_path' => 'projects/showcase.png',
        'is_featured' => $featured,
        'status' => PublishStatus::Published,
    ]);

    $response = $this->get(route('projects.index'));

    $imageUrl = $project->featured_image_url;
    if ($imageUrl === null) {
        throw new RuntimeException('Expected the uploaded project image URL.');
    }

    $response->assertOk()
        ->assertSee($imageUrl, false)
        ->assertSee('showcase-640.webp', false)
        ->assertSee('showcase-1280.webp', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('object-contain', false);
})->with([true, false]);

it('keeps repository URLs out of public project markup and structured data', function (?string $website) {
    $project = Project::query()->create([
        'title' => 'Private repository project',
        'description' => 'Public case study, private source.',
        'github_url' => 'https://github.com/example/confidential-repository',
        'url' => $website,
        'status' => PublishStatus::Published,
    ]);

    $response = $this->get(route('projects.show', $project));

    $response->assertOk()
        ->assertDontSee('confidential-repository', false)
        ->assertDontSee('Explore the code')
        ->assertSee('Discuss a similar project');

    if ($website !== null) {
        $response->assertSee($website, false);
    }

    expect($project->refresh()->github_url)->toBe('https://github.com/example/confidential-repository');
})->with([null, 'https://example.com/product']);

it('renders project content as safe Markdown', function () {
    $project = Project::query()->create([
        'title' => 'Safe project content',
        'description' => 'A public case study.',
        'content' => <<<'MARKDOWN'
## Project approach

This is **rendered** content.

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
        'status' => PublishStatus::Published,
    ]);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSeeHtml('<h2>Project approach</h2>')
        ->assertSeeHtml('This is <strong>rendered</strong> content.')
        ->assertDontSee("<script>alert('unsafe')</script>", false)
        ->assertDontSee('javascript:', false);
});

it('loads only the related projects displayed on a project page', function () {
    $project = Project::query()->create([
        'title' => 'Current Project',
        'slug' => 'current-project',
        'description' => 'The current project.',
        'status' => PublishStatus::Published,
        'sort_order' => 1,
    ]);

    foreach (range(2, 5) as $sortOrder) {
        Project::query()->create([
            'title' => "Related Project {$sortOrder}",
            'slug' => "related-project-{$sortOrder}",
            'description' => "Related project {$sortOrder}.",
            'status' => PublishStatus::Published,
            'sort_order' => $sortOrder,
        ]);
    }

    Project::query()->create([
        'title' => 'Draft Project',
        'slug' => 'draft-project',
        'description' => 'A draft project.',
        'status' => PublishStatus::Draft,
        'sort_order' => 0,
    ]);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertViewHas('otherProjects', fn ($otherProjects): bool => $otherProjects instanceof Collection && $otherProjects->count() === 3)
        ->assertSee('Related Project 2')
        ->assertSee('Related Project 4')
        ->assertDontSee('Related Project 5')
        ->assertDontSee('Draft Project');
});
