<?php

return [
    'backup' => [
        'max_age_hours' => (int) env('BACKUP_MAX_AGE_HOURS', 36),
    ],
    'failed_jobs' => [
        'alert_threshold' => (int) env('QUEUE_FAILED_JOB_ALERT_THRESHOLD', 0),
        'retention_hours' => (int) env('QUEUE_FAILED_JOB_RETENTION_HOURS', 168),
    ],
    'runtime' => [
        'enabled' => (bool) env('RUNTIME_HEALTH_ENABLED', false),
        'max_age_seconds' => (int) env('RUNTIME_HEALTH_MAX_AGE', 300),
    ],
];
