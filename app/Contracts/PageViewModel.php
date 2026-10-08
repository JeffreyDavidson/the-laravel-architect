<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\PageMeta;

/**
 * The ViewModel of a public HTML page. Its data(), and previewData() when the page has
 * previews, return the view data with the page's PageMeta under the PAGE_META key, and the
 * site layout reads the SEO tags and JSON-LD from that value only. ViewModels stay stateless,
 * so each method documents the key in its array shape (`pageMeta: PageMeta`), PHPStan checks
 * the shape, and tests/Architecture/ViewModelArchitectureTest.php checks every page ViewModel.
 *
 * @see PageMeta
 */
interface PageViewModel
{
    /** The view-data key that holds the page's PageMeta. */
    public const string PAGE_META = 'pageMeta';
}
