<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublicationState;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\ListEpisodes;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\ListPosts;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('filters content by publication state', function (string $type, string $page, PublicationState $state, array $expected) {
    /** @var list<string> $expected */
    freezeSecond();
    $content = collect([
        'live' => ['published_at' => now()->subDay()],
        'scheduled' => ['published_at' => now()->addDay()],
        'draft' => ['published_at' => null],
    ])->map(function (array $attributes, string $key) use ($type): Post|Project|Episode|NewsletterIssue {
        $record = PublishableFixtures::ready($type, ['slug' => "{$key}-content", ...$attributes]);

        if ($key !== 'draft') {
            $record->publish();
        }

        return $record;
    });

    livewire($page)
        ->filterTable('publication', $state)
        ->assertCanSeeTableRecords($content->only($expected))
        ->assertCanNotSeeTableRecords($content->except($expected));
})->with([
    'post' => ['post', ListPosts::class],
    'episode' => ['episode', ListEpisodes::class],
])->with([
    'live' => [PublicationState::Live, ['live']],
    'scheduled' => [PublicationState::Scheduled, ['scheduled']],
    'not yet published' => [PublicationState::Unpublished, ['scheduled', 'draft']],
]);
