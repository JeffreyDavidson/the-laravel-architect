<?php

declare(strict_types=1);

namespace App\Support\Monitoring\Sentry;

use Sentry\Breadcrumb;

final readonly class RedactSentryBreadcrumb
{
    public function __construct(private SanitizeSentryData $sanitizer) {}

    public function __invoke(Breadcrumb $breadcrumb): Breadcrumb
    {
        return $this->sanitizer->breadcrumb($breadcrumb);
    }
}
