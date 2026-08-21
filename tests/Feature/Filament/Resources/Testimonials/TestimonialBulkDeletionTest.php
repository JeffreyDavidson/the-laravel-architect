<?php

use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Testimonial;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected testimonials through the table bulk action', function () {
    $testimonials = collect([
        Testimonial::query()->create([
            'name' => 'First bulk delete testimonial',
            'role' => 'Developer',
            'company' => 'Laravel Community',
            'body' => 'The first testimonial selected for bulk deletion.',
            'status' => 'approved',
            'sort_order' => 1,
        ]),
        Testimonial::query()->create([
            'name' => 'Second bulk delete testimonial',
            'role' => 'Designer',
            'company' => 'Laravel Community',
            'body' => 'The second testimonial selected for bulk deletion.',
            'status' => 'pending',
            'sort_order' => 2,
        ]),
    ]);

    livewire(ListTestimonials::class)
        ->selectTableRecords($testimonials)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    expect(Testimonial::query()->whereKey($testimonials->pluck('id'))->count())->toBe(0);
});
