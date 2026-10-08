<?php

use App\Contracts\PageViewModel;
use App\ViewModels\NewsletterRssFeedViewModel;
use App\ViewModels\RobotsTxtViewModel;
use App\ViewModels\RssFeedViewModel;
use App\ViewModels\SitemapViewModel;
use App\ViewModels\SiteStructuredData;
use Illuminate\Support\Facades\File;

/*
 * The feed ViewModels shape RSS, sitemap and robots.txt output rather than an HTML page, and
 * SiteStructuredData holds the site-wide JSON-LD the page ViewModels share. Every other class in
 * App\ViewModels is a page ViewModel.
 */
arch('makes every page ViewModel a PageViewModel')
    ->expect('App\ViewModels')
    ->classes()
    ->toImplement(PageViewModel::class)
    ->ignoring([
        NewsletterRssFeedViewModel::class,
        RobotsTxtViewModel::class,
        RssFeedViewModel::class,
        SitemapViewModel::class,
        SiteStructuredData::class,
        'App\ViewModels\Concerns',
    ]);

it('returns the page meta from every page rendering method of a page ViewModel', function () {
    $checked = 0;

    foreach (File::files(app_path('ViewModels')) as $file) {
        $class = 'App\\ViewModels\\'.$file->getBasename('.php');

        if (! class_exists($class) || ! is_subclass_of($class, PageViewModel::class)) {
            continue;
        }

        foreach (['data', 'previewData'] as $method) {
            if (! method_exists($class, $method)) {
                continue;
            }

            $docComment = (string) new ReflectionMethod($class, $method)->getDocComment();

            expect(str_contains($docComment, PageViewModel::PAGE_META.': PageMeta'))
                ->toBeTrue("{$class}::{$method}() must document its PageMeta.");
            $checked++;
        }
    }

    expect($checked)->toBeGreaterThan(20);
});
