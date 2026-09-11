<?php

namespace App\ViewModels;

use App\Models\Subscriber;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class NewsletterUnsubscriptionViewModel
{
    /** @return array{actionUrl: string, subscriber: Subscriber, seoSource: SEOData} */
    public function data(Subscriber $subscriber, string $actionUrl): array
    {
        return [
            'actionUrl' => $actionUrl,
            'subscriber' => $subscriber,
            'seoSource' => (new SEOData(
                title: 'Unsubscribe',
                description: 'Manage your subscription to The Laravel Architect newsletter.',
            ))->markAsNoindex(),
        ];
    }
}
