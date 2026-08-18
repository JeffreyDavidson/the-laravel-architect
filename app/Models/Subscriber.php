<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

#[Fillable('email', 'subscribed_at', 'verified_at', 'verification_token', 'unsubscribed_at')]
class Subscriber extends Model
{
    public function unsubscribeUrl(): string
    {
        return URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $this]);
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
