<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\NewsletterIssue;
use Illuminate\Database\Eloquent\Collection;

final class NewsletterRssFeedQuery
{
    /**
     * The twenty newest published newsletter issues, newest first.
     *
     * @return Collection<int, NewsletterIssue>
     */
    public function get(): Collection
    {
        return NewsletterIssue::query()->published()
            ->latest('published_at')
            ->latest('id')
            ->take(20)
            ->get();
    }
}
