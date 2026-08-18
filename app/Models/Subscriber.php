<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('email', 'subscribed_at', 'verified_at', 'verification_token', 'unsubscribed_at')]
class Subscriber extends Model
{
    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'verified_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
