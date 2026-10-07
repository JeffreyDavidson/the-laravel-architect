<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SuppressionReason;
use Carbon\CarbonInterface;
use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Date;

/**
 * @property string $email
 * @property CarbonInterface $subscribed_at
 * @property CarbonInterface|null $verified_at
 * @property string|null $verification_token_hash
 * @property CarbonInterface|null $unsubscribed_at
 * @property CarbonInterface|null $suppressed_at
 * @property SuppressionReason|null $suppression_reason
 *
 * @method static Builder<static> active()
 */
#[Fillable('email', 'subscribed_at', 'verified_at', 'unsubscribed_at', 'suppressed_at', 'suppression_reason')]
#[Hidden('verification_token_hash')]
final class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory, Prunable;

    private const int UNCONFIRMED_RETENTION_DAYS = 7;

    private const int UNSUBSCRIBED_RETENTION_DAYS = 30;

    public function isSuppressed(): bool
    {
        return $this->suppressed_at !== null;
    }

    public function isActive(): bool
    {
        return $this->verified_at !== null
            && $this->unsubscribed_at === null
            && $this->suppressed_at === null;
    }

    /**
     * Unconfirmed sign-ups are kept briefly after their one-day confirmation
     * link expires, and unsubscribed addresses briefly so a repeated
     * unsubscribe click still resolves. Suppressed addresses (bounces and
     * spam complaints) are never pruned, so they cannot be mailed again.
     *
     * @return Builder<Subscriber>
     */
    public function prunable(): Builder
    {
        return Subscriber::query()
            ->whereNull('suppressed_at')
            ->where(function (Builder $query): void {
                $query
                    ->where(function (Builder $query): void {
                        $query
                            ->whereNull('verified_at')
                            ->where(
                                'subscribed_at',
                                '<=',
                                Date::now()->subDays(self::UNCONFIRMED_RETENTION_DAYS),
                            );
                    })
                    ->orWhere(
                        'unsubscribed_at',
                        '<=',
                        Date::now()->subDays(self::UNSUBSCRIBED_RETENTION_DAYS),
                    );
            });
    }

    /** @param Builder<Subscriber> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query
            ->whereNotNull('verified_at')
            ->whereNull('unsubscribed_at')
            ->whereNull('suppressed_at');
    }

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'verified_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'suppressed_at' => 'datetime',
            'suppression_reason' => SuppressionReason::class,
        ];
    }
}
