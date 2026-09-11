<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait Featurable
{
    /** @param Builder<static> $query */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function isFeatured(): bool
    {
        return $this->getAttribute('is_featured') === true;
    }
}
