<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\NewsletterSubscriber;
use JeffreyDavidson\CreatorKit\Enums\SubscriberStatus;
use JeffreyDavidson\CreatorKit\Enums\SuppressionReason;
use JeffreyDavidson\CreatorKit\Models\Concerns\IsNewsletterSubscriber;

/**
 * A newsletter subscriber. Its status, confirmation-token check, `active()` and
 * `withStatus()` scopes, casts and pruning rules come from creator-kit's
 * `IsNewsletterSubscriber`.
 *
 * @property string $email
 * @property CarbonInterface $subscribed_at
 * @property CarbonInterface|null $verified_at
 * @property string|null $verification_token_hash
 * @property CarbonInterface|null $unsubscribed_at
 * @property CarbonInterface|null $suppressed_at
 * @property SuppressionReason|null $suppression_reason
 *
 * @method static Builder<static> active()
 * @method static Builder<static> withStatus(SubscriberStatus $status)
 */
#[Fillable('email', 'subscribed_at', 'verified_at', 'unsubscribed_at', 'suppressed_at', 'suppression_reason')]
#[Hidden('verification_token_hash')]
final class Subscriber extends Model implements NewsletterSubscriber
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory, IsNewsletterSubscriber;
}
