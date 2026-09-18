<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable('email', 'subscribed_at', 'verified_at', 'unsubscribed_at')]
#[Hidden('verification_token_hash')]
class Subscriber extends Model
{
    public function isActive(): bool
    {
        return $this->verified_at !== null && $this->unsubscribed_at === null;
    }

    /** @param Builder<Subscriber> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNotNull('verified_at')->whereNull('unsubscribed_at');
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
