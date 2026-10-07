<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\Subscriber;
use Illuminate\Contracts\Routing\UrlGenerator;

final readonly class SubscriberPresenter
{
    public function __construct(
        private Subscriber $subscriber,
        private UrlGenerator $urls,
    ) {}

    public static function from(Subscriber $subscriber): self
    {
        return app()->make(self::class, ['subscriber' => $subscriber]);
    }

    /**
     * The signed link that confirms a subscription request. It carries the plain token whose
     * hash the subscriber stores, and it expires after a day.
     */
    public function confirmationUrl(string $token): string
    {
        return $this->urls->temporarySignedRoute(
            'newsletter.confirm',
            now()->addDay(),
            ['subscriber' => $this->subscriber, 'token' => $token],
        );
    }

    /**
     * Newsletter links must keep working for as long as the email can be
     * read, so they do not expire. The signature still binds the link to one
     * subscriber, and the same URL accepts one-click unsubscribe POSTs.
     */
    public function unsubscribeUrl(): string
    {
        return $this->urls->signedRoute('newsletter.unsubscribe', ['subscriber' => $this->subscriber]);
    }
}
