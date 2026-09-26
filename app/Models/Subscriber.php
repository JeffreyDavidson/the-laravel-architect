<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

#[Fillable('email', 'subscribed_at', 'verified_at', 'unsubscribed_at')]
#[Hidden('verification_token_hash')]
class Subscriber extends Model
{
    use Prunable;

    private const int UNCONFIRMED_RETENTION_DAYS = 7;

    private const int UNSUBSCRIBED_RETENTION_DAYS = 30;

    public function isActive(): bool
    {
        return $this->verified_at !== null
            && $this->unsubscribed_at === null;
    }

    /**
     * Unconfirmed sign-ups are kept briefly after their one-day confirmation
     * link expires, and unsubscribed addresses briefly so a repeated
     * unsubscribe click still resolves. Resubscribing always requires
     * confirmation again, so no suppression list is retained.
     *
     * @return Builder<Subscriber>
     */
    public function prunable(): Builder
    {
        return Subscriber::query()
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('verified_at')
                    ->where(
                        'subscribed_at',
                        '<=',
                        now()->subDays(self::UNCONFIRMED_RETENTION_DAYS),
                    );
            })
            ->orWhere(
                'unsubscribed_at',
                '<=',
                now()->subDays(self::UNSUBSCRIBED_RETENTION_DAYS),
            );
    }

    /** @param Builder<Subscriber> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query
            ->whereNotNull('verified_at')
            ->whereNull('unsubscribed_at');
    }

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'verified_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
