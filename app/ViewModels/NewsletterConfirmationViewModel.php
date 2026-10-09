<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Subscriber;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class NewsletterConfirmationViewModel implements PageViewModel
{
    /** @return array{actionUrl: string, subscriber: Subscriber, pageMeta: PageMeta} */
    public function data(Subscriber $subscriber, string $actionUrl): array
    {
        return [
            'actionUrl' => $actionUrl,
            'subscriber' => $subscriber,
            'pageMeta' => new PageMeta(new SEOData(
                title: 'Confirming Your Subscription',
                description: 'Confirm your subscription to The Laravel Architect newsletter.',
            )->markAsNoindex()),
        ];
    }
}
