<?php

/**
 * FilaCheck Pro rule overrides. Rules not listed here keep their defaults (enabled).
 */
return [
    /*
     * Navigation badges read their counts from App\Queries\AdminMetricsQuery, the same
     * counts the dashboard shows, and caching lives inside Query classes rather than in
     * Filament (see .ai/rules/filament.md). The badge counts are uncached aggregate queries
     * on small, indexed tables, so a badge cache would only let badges lag the dashboard.
     */
    'navigation-badge-not-cached' => [
        'enabled' => false,
    ],
];
