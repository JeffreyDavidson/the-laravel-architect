<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @phpstan-type FathomOverview array{
 *     pageviews: int,
 *     visits: int,
 *     uniques: int,
 *     bounce_rate: int,
 *     newsletter_signups: int,
 *     contact_submissions: int,
 *     project_live_link_clicks: int,
 *     github_profile_clicks: int,
 * }
 */
final class FathomAnalyticsService
{
    private const string BASE_URL = 'https://api.usefathom.com/v1';

    private const string CACHE_KEY_PREFIX = 'fathom.analytics.overview';

    /** @var array<string, string> */
    private const array EVENT_NAMES = [
        'newsletter_signups' => 'newsletter signup',
        'contact_submissions' => 'contact form submission',
        'project_live_link_clicks' => 'project live link click',
        'github_profile_clicks' => 'github profile click',
    ];

    public static function isConfigured(): bool
    {
        return self::stringConfig('site_id') !== '' && self::stringConfig('api_token') !== '';
    }

    /**
     * @return FathomOverview|null
     */
    public static function overview(): ?array
    {
        $siteId = self::stringConfig('site_id');
        $token = self::stringConfig('api_token');

        if ($siteId === '' || $token === '') {
            return null;
        }

        return Cache::remember(
            self::CACHE_KEY_PREFIX.'.'.$siteId,
            now()->addMinutes(10),
            fn (): ?array => self::fetchOverview($siteId, $token),
        );
    }

    /**
     * @return FathomOverview|null
     */
    private static function fetchOverview(string $siteId, string $token): ?array
    {
        try {
            $dateFrom = now()->subDays(30)->toDateString();
            $dateTo = now()->toDateString();
            $pageviews = self::aggregation(self::request($token, [
                'entity' => 'pageview',
                'entity_id' => $siteId,
                'aggregates' => 'visits,uniques,pageviews,avg_duration,bounce_rate',
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]));

            $events = [];

            foreach (self::EVENT_NAMES as $key => $eventName) {
                try {
                    $events[$key] = self::integer(self::aggregation(self::request($token, [
                        'entity' => 'event',
                        'site_id' => $siteId,
                        'entity_name' => $eventName,
                        'aggregates' => 'unique_conversions',
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                    ]))['unique_conversions'] ?? 0);
                } catch (Throwable) {
                    $events[$key] = 0;
                }
            }

            return [
                'pageviews' => self::integer($pageviews['pageviews'] ?? 0),
                'visits' => self::integer($pageviews['visits'] ?? 0),
                'uniques' => self::integer($pageviews['uniques'] ?? 0),
                'bounce_rate' => self::percentage($pageviews['bounce_rate'] ?? 0),
                'newsletter_signups' => $events['newsletter_signups'],
                'contact_submissions' => $events['contact_submissions'],
                'project_live_link_clicks' => $events['project_live_link_clicks'],
                'github_profile_clicks' => $events['github_profile_clicks'],
            ];
        } catch (Throwable $exception) {
            Log::warning('Unable to refresh Fathom analytics.', [
                'exception' => $exception::class,
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, string>  $query
     */
    private static function request(string $token, array $query): Response
    {
        return self::client($token)->get('/aggregations', $query)->throw();
    }

    private static function client(string $token): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withToken($token)
            ->acceptJson()
            ->connectTimeout(2)
            ->timeout(5)
            ->retry(2, 100);
    }

    /**
     * @return array<string, mixed>
     */
    private static function aggregation(Response $response): array
    {
        $data = $response->json();

        if (! is_array($data) || ! isset($data[0]) || ! is_array($data[0])) {
            return [];
        }

        $aggregation = [];

        foreach ($data[0] as $key => $value) {
            if (is_string($key)) {
                $aggregation[$key] = $value;
            }
        }

        return $aggregation;
    }

    private static function integer(mixed $value): int
    {
        return is_numeric($value) ? (int) round((float) $value) : 0;
    }

    private static function percentage(mixed $value): int
    {
        if (! is_numeric($value)) {
            return 0;
        }

        $percentage = (float) $value;
        $percentage = $percentage <= 1 ? $percentage * 100 : $percentage;

        return max(0, min(100, (int) round($percentage)));
    }

    private static function stringConfig(string $key): string
    {
        $value = config("services.fathom.{$key}");

        return is_string($value) ? trim($value) : '';
    }
}
