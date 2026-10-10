<?php

return [
    'failed_jobs' => [
        'retention_hours' => (int) env('QUEUE_FAILED_JOB_RETENTION_HOURS', 168),
    ],
    'runtime' => [
        'enabled' => (bool) env('RUNTIME_HEALTH_ENABLED', false),
        // How old the scheduler and queue worker heartbeats may be, in seconds, before the health check fails. Must be at least 60.
        'max_age_seconds' => (int) env('RUNTIME_HEALTH_MAX_AGE', 300),
    ],
];
