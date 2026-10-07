<?php

use App\Data\CalendarEntry;
use App\Filament\Pages\EditorialCalendar;
use App\Models\Episode;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

afterEach(function () {
    Date::setTestNow();
});

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('renders the calendar route for an authorized user', function () {
    get(EditorialCalendar::getUrl())
        ->assertOk()
        ->assertSee('Editorial Calendar');
});

it('shows scheduled and review content in its publication month', function () {
    Date::setTestNow(Carbon::parse('2026-09-15 12:00:00'));
    $post = Post::factory()
        ->inReview()
        ->create(['published_at' => '2026-09-18 09:00:00']);
    $episode = Episode::factory()
        ->scheduled()
        ->create(['published_at' => '2026-09-22 09:00:00']);

    livewire(EditorialCalendar::class)
        ->assertSee('September 2026')
        ->assertSee($post->title)
        ->assertSee('In Review')
        ->assertSee($episode->title)
        ->assertSee('Scheduled');
});

it('places content on its publication day in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    Date::setTestNow(Carbon::parse('2026-10-01 12:00:00'));
    Post::factory()->create([
        'title' => 'Evening post',
        'published_at' => '2026-10-06 01:00:00',
    ]);

    livewire(EditorialCalendar::class)
        ->assertViewHas('weeks', function (Collection $weeks): bool {
            /** @var Collection<int, Collection<int, array{date: string, entries: Collection<int, CalendarEntry>}>> $weeks */
            foreach ($weeks as $week) {
                foreach ($week as $day) {
                    if ($day['date'] !== '2026-10-05') {
                        continue;
                    }

                    $titles = $day['entries']
                        ->pluck('title')
                        ->all();

                    return $titles === ['Evening post'];
                }
            }

            return false;
        });
});

it('keeps undated content in the unscheduled queue and filters other months', function () {
    Date::setTestNow(Carbon::parse('2026-09-15 12:00:00'));
    $unscheduledPost = Post::factory()->create();
    $olderPost = Post::factory()
        ->published()
        ->create(['published_at' => '2026-07-18 09:00:00']);

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
