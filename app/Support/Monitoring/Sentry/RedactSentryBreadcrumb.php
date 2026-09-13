<?php

namespace App\Support\Monitoring\Sentry;

use Sentry\Breadcrumb;

final class RedactSentryBreadcrumb
{
    public function __construct(private readonly SanitizeSentryData $sanitizer) {}

    public function __invoke(Breadcrumb $breadcrumb): Breadcrumb
    {
        return $this->sanitizer->breadcrumb($breadcrumb);
    }
}
