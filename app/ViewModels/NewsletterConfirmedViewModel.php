<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class NewsletterConfirmedViewModel implements PageViewModel
{
    /** @return array{latestPosts: Collection<int, Post>, pageMeta: PageMeta} */
    public function data(): array
    {
        return [
            'latestPosts' => Post::query()->published()
                ->with('category')
                ->latest('published_at')
                ->take(3)
                ->get(),
            'pageMeta' => new PageMeta(new SEOData(
                title: 'You’re Confirmed',
                description: 'Your subscription to The Laravel Architect newsletter is confirmed.',
            )->markAsNoindex()),
        ];
    }
}
