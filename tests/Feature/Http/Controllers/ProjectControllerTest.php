<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Presenters\ProjectPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('shows published projects once in their featured groups without repository links', function () {
    foreach ([['Later featured', true, 2], ['First featured', true, 1], ['Other work', false, 0]] as [$title, $featured, $order]) {
        Project::factory()->published()
            ->create([
                'description' => "Summary for {$title}.",
                'is_featured' => $featured,
                'sort_order' => $order,
                'github_url' => 'https://github.com/example/private-repository',
            ]);
    }

    Project::factory()->create(['title' => 'Unpublished work']);

    $response = get(route('projects.index'));

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
    $response = get(route('projects.index'));

    $response->assertOk()
        ->assertSee('Project details aren’t available here yet.')
        ->assertSeeHtml(route('contact.create'))
        ->assertDontSeeHtml('data-project-entry')
        ->assertDontSeeHtml('featured-projects-heading')
        ->assertDontSeeHtml('more-projects-heading');
});

it('filters published projects by technology and topic', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel']);
    $matchingProject = Project::factory()->published()
        ->create([
            'title' => 'Laravel project',
            'tech_stack' => ['Laravel', 'Filament'],
        ]);
    $matchingProject->attachTag($tag);
    Project::factory()->published()
        ->create([
            'title' => 'Vue project',
            'tech_stack' => ['Vue'],
        ]);

    get(route('projects.index', ['technology' => 'laravel', 'tag' => 'laravel']))
        ->assertOk()
        ->assertSee('Laravel project')
        ->assertDontSee('Vue project')
        ->assertSeeHtml('value="Laravel" selected')
        ->assertSeeHtml('value="laravel" selected');
});

it('explains when valid project filters have no matching projects', function () {
    $laravelTag = Tag::factory()->create(['name' => 'Laravel']);
    $vueTag = Tag::factory()->create(['name' => 'Vue']);

    $laravelProject = Project::factory()->published()
        ->create([
            'title' => 'Laravel project',
            'tech_stack' => ['Laravel'],
        ]);
    $laravelProject->attachTag($laravelTag);
    $vueProject = Project::factory()->published()
        ->create([
            'title' => 'Vue project',
            'tech_stack' => ['Vue'],
        ]);
    $vueProject->attachTag($vueTag);

    get(route('projects.index', ['technology' => 'Laravel', 'tag' => 'vue']))
        ->assertOk()
        ->assertSee('No projects match those filters.')
        ->assertSeeHtml(route('projects.index'))
        ->assertDontSee('Laravel project')
        ->assertDontSee('Vue project');
});

it('uses responsive uploaded images in either project group', function (bool $featured) {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('showcase.png', 1280, 720);
    Storage::disk('public')->put('projects/showcase.png', $image->getContent());

    $project = Project::factory()->published()
        ->create([
            'featured_image_path' => 'projects/showcase.png',
            'is_featured' => $featured,
        ]);

    $response = get(route('projects.index'));

    $imageUrl = ProjectPresenter::from($project)->featuredImageUrl();
    if ($imageUrl === null) {
        throw new RuntimeException('Expected the uploaded project image URL.');
    }

    $response->assertOk()
        ->assertSeeHtml($imageUrl)
        ->assertSeeHtml('showcase-640.webp')
        ->assertSeeHtml('showcase-1280.webp')
        ->assertSeeHtml('fetchpriority="high"')
        ->assertSeeHtml('object-contain');
})->with([true, false]);

it('keeps repository URLs out of public project markup and structured data', function (?string $website) {
    $project = Project::factory()->published()
        ->create([
            'github_url' => 'https://github.com/example/confidential-repository',
            'url' => $website,
        ]);

    $response = get(route('projects.show', $project));

    $response->assertOk()
        ->assertDontSeeHtml('confidential-repository')
        ->assertDontSee('Explore the code')
        ->assertSee('Discuss a similar project');

    if ($website !== null) {
        $response->assertSeeHtml($website);
    }

    expect($project->refresh()
        ->github_url)->toBe('https://github.com/example/confidential-repository');
})->with([null, 'https://example.com/product']);

it('renders project content as safe Markdown', function () {
    $project = Project::factory()->published()
        ->create([
            'content' => <<<'MARKDOWN'
## Project approach

This is **rendered** content.

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
        ]);

    get(route('projects.show', $project))
        ->assertOk()
        ->assertSeeHtml('id="project-story"')
        ->assertSee('Project story')
        ->assertSeeHtml('<h2>Project approach</h2>')
        ->assertSeeHtml('This is <strong>rendered</strong> content.')
        ->assertDontSeeHtml("<script>alert('unsafe')</script>")
        ->assertDontSeeHtml('javascript:');
});

it('renders project metadata without filter links', function () {
    $tag = Tag::factory()->create(['name' => 'Architecture']);
    $project = Project::factory()->published()
        ->create([
            'title' => 'Metadata project',
            'tech_stack' => ['Laravel'],
        ]);
    $project->attachTag($tag);

    get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Project story')
        ->assertSeeHtml('aria-label="Technologies used for Metadata project"')
        ->assertDontSeeHtml('href="'.route('projects.index', ['technology' => 'Laravel']).'"')
        ->assertSeeHtml('aria-label="Topics covered by Metadata project"')
        ->assertDontSeeHtml('href="'.route('projects.index', ['tag' => 'architecture']).'"')
        ->assertSeeHtml('Metadata project');
});

it('shows published writing and podcast episodes connected by project tags', function () {
    $podcast = Podcast::factory()->create();
    $tag = Tag::factory()->create(['name' => 'Architecture']);
    $project = Project::factory()->published()
        ->create();
    $project->attachTag($tag);

    $post = Post::factory()->published()
        ->create(['title' => 'Connected article']);
    $post->attachTag($tag);

    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create(['title' => 'Connected episode']);
    $episode->attachTag($tag);

    get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Keep exploring')
        ->assertSee('Connected article')
        ->assertSee('Listen next')
        ->assertSee('Connected episode')
        ->assertSeeHtml(route('blog.show', $post))
        ->assertSeeHtml(route('podcast.episode', [$podcast, $episode]));
});

it('loads only the related projects displayed on a project page', function () {
    $project = Project::factory()->published()
        ->create(['sort_order' => 1]);

    foreach (range(2, 5) as $sortOrder) {
        Project::factory()->published()
            ->create([
                'title' => "Related Project {$sortOrder}",
                'sort_order' => $sortOrder,
            ]);
    }

    Project::factory()->create([
        'title' => 'Draft Project',
        'sort_order' => 0,
    ]);

    get(route('projects.show', $project))
        ->assertOk()
        ->assertViewHas('otherProjects', fn (mixed $otherProjects): bool => $otherProjects instanceof Collection && $otherProjects->count() === 3)
        ->assertSee('Related Project 2')
        ->assertSee('Related Project 4')
        ->assertDontSee('Related Project 5')
        ->assertDontSee('Draft Project');
});

it('shares a project featured image in social cards', function () {
    Storage::fake('public', ['url' => config('filesystems.disks.public.url')]);
    $project = Project::factory()->published()
        ->create(['featured_image_path' => 'projects/showcase.png']);
    $imageUrl = ProjectPresenter::from($project)->featuredImageUrl();
    if ($imageUrl === null) {
        throw new RuntimeException('Expected the uploaded project image URL.');
    }

    $response = get(route('projects.show', $project));

    $response->assertOk()
        ->assertSeeHtml("<meta property=\"og:image\" content=\"{$imageUrl}\">")
        ->assertSeeHtml('<meta name="twitter:card" content="summary_large_image">')
        ->assertDontSeeHtml('logo-color-black-bg.png');
});

it('shares the site image for a project without a featured image', function () {
    $project = Project::factory()->published()
        ->create();

    $response = get(route('projects.show', $project));

    $response->assertOk()
        ->assertSeeHtml('<meta property="og:image" content="'.secure_url('/images/logo-color-black-bg.png').'">')
        ->assertSeeHtml('<meta name="twitter:card" content="summary">');
});
