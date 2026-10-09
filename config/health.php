<?php

return [
    'failed_jobs' => [
        'retention_hours' => (int) env('QUEUE_FAILED_JOB_RETENTION_HOURS', 168),
    ],
    'runtime' => [
        'enabled' => (bool) env('RUNTIME_HEALTH_ENABLED', false),
    ],
];
