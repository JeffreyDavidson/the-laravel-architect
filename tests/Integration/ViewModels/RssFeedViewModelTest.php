<?php

use App\ViewModels\RssFeedViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('takes the channel title and description from config', function () {
    config()->set('seo.site_name', 'Site & Co');
    config()->set('seo.feed_description', 'Notes on <code> & more.');

    $channel = app(RssFeedViewModel::class)->data();

    expect($channel)
        ->toMatchArray([
            'title' => 'Site & Co',
            'description' => 'Notes on <code> & more.',
            'feedUrl' => route('rss'),
        ]);
});
