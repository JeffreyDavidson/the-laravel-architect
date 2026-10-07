<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Support\Seo\StructuredDataBuilder;
use Illuminate\View\View;

/**
 * Gives public pages and the 404 page the JSON-LD schemas built from the data their view received.
 */
final readonly class StructuredDataComposer
{
    public function __construct(private StructuredDataBuilder $structuredDataBuilder) {}

    public function compose(View $view): void
    {
        /** @var array<string, mixed> $pageData */
        $pageData = $view->getData();

        $view->with('structuredData', $this->structuredDataBuilder->build($pageData));
    }
}
