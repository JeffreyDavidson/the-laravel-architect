<?php

namespace App\ViewModels;

use App\Models\Subscriber;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class NewsletterConfirmationViewModel
{
    /** @return array{actionUrl: string, subscriber: Subscriber, seoSource: SEOData} */
    public function data(Subscriber $subscriber, string $actionUrl): array
    {
        return [
            'actionUrl' => $actionUrl,
            'subscriber' => $subscriber,
            'seoSource' => (new SEOData(
                title: 'Confirm Your Subscription',
                description: 'Confirm your subscription to The Laravel Architect newsletter.',
            ))->markAsNoindex(),
        ];
    }
}
