<?php

use App\Services\FathomAnalyticsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config()->set([
        'services.fathom.site_id' => 'site-id',
        'services.fathom.api_token' => 'test-token',
    ]);
});

it('does not make requests when Fathom is not configured', function () {
    config()->set([
        'services.fathom.site_id' => null,
        'services.fathom.api_token' => null,
    ]);
    Http::preventStrayRequests();

    expect(FathomAnalyticsService::overview())->toBeNull();
});

it('returns cached traffic and conversion aggregates', function () {
    Http::fake(function (Request $request) {
        expect($request->hasHeader('Authorization', 'Bearer test-token'))->toBeTrue();

        return $request->data()['entity'] === 'pageview'
            ? Http::response([[
                'pageviews' => '1234',
                'visits' => '567',
                'uniques' => '456',
                'bounce_rate' => '0.42',
            ]])
            : Http::response([['unique_conversions' => '7']]);
    });

    $overview = FathomAnalyticsService::overview();

    expect($overview)->toMatchArray([
        'pageviews' => 1234,
        'visits' => 567,
        'uniques' => 456,
        'bounce_rate' => 42,
        'newsletter_signups' => 7,
        'contact_submissions' => 7,
        'project_live_link_clicks' => 7,
        'github_profile_clicks' => 7,
    ])
        ->and(FathomAnalyticsService::overview())->toEqual($overview);

    Http::assertSentCount(5);
});

it('returns no overview when the pageview request fails', function () {
    Http::fake(fn () => Http::response([], 500));

    expect(FathomAnalyticsService::overview())->toBeNull();
});
