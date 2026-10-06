<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class NewsletterConfirmedViewModel
{
    /** @return array{latestPosts: Collection<int, Post>, seoSource: SEOData} */
    public function data(): array
    {
        return [
            'latestPosts' => Post::published()
                ->with('category')
                ->latest('published_at')
                ->take(3)
                ->get(),
            'seoSource' => new SEOData(
                title: 'You’re Confirmed',
                description: 'Your subscription to The Laravel Architect newsletter is confirmed.',
            )->markAsNoindex(),
        ];
    }
}
