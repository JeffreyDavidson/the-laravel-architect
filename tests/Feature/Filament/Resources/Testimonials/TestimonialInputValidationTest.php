<?php

use App\Enums\TestimonialStatus;
use App\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('rejects testimonial text inputs longer than their database columns', function (string $field) {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $testimonial = Testimonial::query()->create([
        'name' => 'Testimonial name',
        'body' => 'Testimonial body.',
        'status' => TestimonialStatus::Pending,
    ]);

    livewire(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
        ->fillForm([$field => str_repeat('a', 256)])
        ->call('save')
        ->assertHasFormErrors([$field => 'max']);

    expect($testimonial->refresh()->{$field})->not->toBe(str_repeat('a', 256));
})->with([
    'name',
    'role',
    'company',
]);
