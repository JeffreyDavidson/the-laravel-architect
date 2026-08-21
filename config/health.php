<?php

return [
    'runtime' => [
        'enabled' => (bool) env('RUNTIME_HEALTH_ENABLED', false),
        'max_age_seconds' => (int) env('RUNTIME_HEALTH_MAX_AGE', 300),
    ],
];
