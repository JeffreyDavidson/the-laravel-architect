<?php

use App\Filament\Widgets\FathomTrafficOverview;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('explains when Fathom is not configured', function () {
    config()->set([
        'services.fathom.site_id' => null,
        'services.fathom.api_token' => null,
    ]);

    $widget = new class extends FathomTrafficOverview
    {
        /** @return list<Stat> */
        public function stats(): array
        {
            return $this->getStats();
        }
    };

    expect($widget->stats()[0]->getValue())->toBe('Not configured');

    livewire(FathomTrafficOverview::class)
        ->assertSee('Fathom traffic')
        ->assertSee('Not configured')
        ->assertSee('Set FATHOM_API_TOKEN to show traffic.');
});

it('shows an unavailable state when configured analytics cannot be loaded', function () {
    config()->set([
        'services.fathom.site_id' => 'site-id',
        'services.fathom.api_token' => 'test-token',
    ]);
    Http::preventStrayRequests();

    livewire(FathomTrafficOverview::class)
        ->assertSee('Unavailable')
        ->assertSee('Check the API token and try again.');
});
