<?php

namespace Tests\Browser\Components;

use Pest\Browser\Api\AwaitableWebpage;

final readonly class NewsletterForm
{
    public function __construct(private AwaitableWebpage $page) {}

    public function submit(string $email): AwaitableWebpage
    {
        $this->page->fill('Email address', $email);
        $this->page->press('Subscribe');

        return $this->page;
    }
}
