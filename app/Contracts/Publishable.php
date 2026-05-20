<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Publishable
{
    public function isPublished(): bool;

    public function scopePublished(Builder $query): Builder;
}
