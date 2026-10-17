<?php

use App\Models\NewsletterDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use JeffreyDavidson\CreatorKit\Jobs\DeliverNewsletterIssue;

pest()->use(RefreshDatabase::class);

it('lets a newsletter delivery time out before the database queue hands it to another worker', function () {
    // Arrange
    $queue = Queue::connection('database');
    $queue->push(new DeliverNewsletterIssue(NewsletterDelivery::factory()->create()));

    // Act
    $queuedJob = $queue->pop();

    // Assert
    expect($queuedJob?->timeout())
        ->toBeInt()
        ->toBeLessThan(config()->integer('queue.connections.database.retry_after'));
});
