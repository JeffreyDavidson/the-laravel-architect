<?php

namespace App\Models;

use Database\Factories\NewsletterDeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One subscriber's copy of a sent newsletter issue. The unique issue and
 * subscriber pair keeps sends idempotent; subscriber email addresses are
 * never copied here, so pruning a subscriber removes their deliveries.
 *
 * @property Carbon|null $sent_at
 * @property-read NewsletterIssue|null $newsletterIssue
 * @property-read Subscriber|null $subscriber
 */
#[Fillable('newsletter_issue_id', 'subscriber_id', 'sent_at')]
class NewsletterDelivery extends Model
{
    /** @use HasFactory<NewsletterDeliveryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<NewsletterIssue, $this> */
    public function newsletterIssue(): BelongsTo
    {
        return $this->belongsTo(NewsletterIssue::class);
    }

    /** @return BelongsTo<Subscriber, $this> */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}
