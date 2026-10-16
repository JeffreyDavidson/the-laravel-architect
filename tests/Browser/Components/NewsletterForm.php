<?php

namespace Tests\Browser\Components;

use Pest\Browser\Api\AwaitableWebpage;

final readonly class NewsletterForm
{
    public function __construct(private AwaitableWebpage $page) {}

    /** Submits once through Playwright: Pest retries a chained press, which could send the sign-up twice. */
    public function submit(string $email): AwaitableWebpage
    {
        $this->page->fill('Email address', $email);
        $this->page->page()
            ->locator('[data-newsletter-form]')
            ->getByRole('button', ['name' => 'Subscribe'])
            ->click();

        return $this->page;
    }
}
