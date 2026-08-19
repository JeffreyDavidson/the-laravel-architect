<?php

use App\Enums\PublishStatus;
use App\Enums\TestimonialStatus;
use App\Filament\Widgets\RecentActivityWidget;
use App\Models\Post;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('renders an empty state when there is no recent activity', function () {
    livewire(RecentActivityWidget::class)
        ->assertSee('No activity yet');
});

it('renders the newest posts and testimonials in chronological order', function () {
    $user = User::factory()->create();

    $posts = [
        ['Old post', PublishStatus::Draft, '2026-08-19 09:00:00'],
        ['Published post', PublishStatus::Published, '2026-08-19 13:00:00'],
        ['Review post', PublishStatus::InReview, '2026-08-19 11:00:00'],
        ['Draft post', PublishStatus::Draft, '2026-08-19 15:00:00'],
    ];

    foreach ($posts as [$title, $status, $updatedAt]) {
        $post = Post::query()->create([
            'title' => $title,
            'slug' => str($title)->slug(),
            'content' => 'Content',
            'user_id' => $user->id,
            'status' => $status,
        ]);

        $post->forceFill(['updated_at' => Carbon::parse($updatedAt)])->saveQuietly();
    }

    $testimonials = [
        ['Old testimonial', TestimonialStatus::Rejected, '2026-08-19 08:00:00'],
        ['Approved testimonial', TestimonialStatus::Approved, '2026-08-19 12:00:00'],
        ['Pending testimonial', TestimonialStatus::Pending, '2026-08-19 14:00:00'],
    ];

    foreach ($testimonials as [$name, $status, $createdAt]) {
        $testimonial = Testimonial::query()->create([
            'name' => $name,
            'body' => 'Testimonial body',
            'status' => $status,
        ]);

        $testimonial->forceFill(['created_at' => Carbon::parse($createdAt)])->saveQuietly();
    }

    livewire(RecentActivityWidget::class)
        ->assertSeeInOrder([
            'Draft post',
            'Pending testimonial',
            'Published post',
            'Approved testimonial',
            'Review post',
        ])
        ->assertSee('Published')
        ->assertSee('Approved')
        ->assertSee('Pending Review')
        ->assertDontSee('Old post')
        ->assertDontSee('Old testimonial');
});
