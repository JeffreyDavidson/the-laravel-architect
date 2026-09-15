<?php

use App\Enums\PublishStatus;
use App\Filament\Pages\EditorialCalendar;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

afterEach(function () {
    Date::setTestNow();
});

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('renders the calendar route for an authorized user', function () {
    $this->get(EditorialCalendar::getUrl())
        ->assertOk()
        ->assertSee('Editorial Calendar');
});

it('shows scheduled and review content in its publication month', function () {
    Date::setTestNow(Carbon::parse('2026-09-15 12:00:00'));
    $post = Post::query()->create([
        'title' => 'Post in review',
        'slug' => 'post-in-review',
        'content' => 'Content',
        'user_id' => auth()->id(),
        'status' => PublishStatus::InReview,
        'published_at' => '2026-09-18 09:00:00',
    ]);
    $podcast = Podcast::query()->create([
        'name' => 'Editorial calendar podcast',
        'slug' => 'editorial-calendar-podcast',
        'description' => 'Description',
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Scheduled episode',
        'slug' => 'scheduled-episode',
        'description' => 'Description',
        'status' => PublishStatus::Scheduled,
        'published_at' => '2026-09-22 09:00:00',
    ]);

    livewire(EditorialCalendar::class)
        ->assertSee('September 2026')
        ->assertSee($post->title)
        ->assertSee('In Review')
        ->assertSee($episode->title)
        ->assertSee('Scheduled');
});

it('keeps undated content in the unscheduled queue and filters other months', function () {
    Date::setTestNow(Carbon::parse('2026-09-15 12:00:00'));
    $unscheduledPost = Post::query()->create([
        'title' => 'Needs a publication date',
        'slug' => 'needs-a-publication-date',
        'content' => 'Content',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Draft,
    ]);
    $olderPost = Post::query()->create([
        'title' => 'Older editorial item',
        'slug' => 'older-editorial-item',
        'content' => 'Content',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Published,
        'published_at' => '2026-07-18 09:00:00',
    ]);

    livewire(EditorialCalendar::class)
        ->assertSee('Unscheduled content')
        ->assertSee($unscheduledPost->title)
        ->assertDontSee($olderPost->title);
});

it('moves between calendar months', function () {
    Date::setTestNow(Carbon::parse('2026-09-15 12:00:00'));

    livewire(EditorialCalendar::class)
        ->assertSee('September 2026')
        ->call('nextMonth')
        ->assertSee('October 2026')
        ->call('previousMonth')
        ->assertSee('September 2026')
        ->call('currentMonth')
        ->assertSee('September 2026');
});
