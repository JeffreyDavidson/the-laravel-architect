<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('email', 'subscribed_at', 'unsubscribed_at')]
class Subscriber extends Model
{
    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
