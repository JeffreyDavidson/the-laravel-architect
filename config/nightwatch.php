<?php

return [
    'enabled' => (bool) env('NIGHTWATCH_ENABLED', false),
    'token' => env('NIGHTWATCH_TOKEN') ?: null,
    'deployment' => env('NIGHTWATCH_DEPLOY', env('LARAVEL_CLOUD_DEPLOY_UUID', env('FORGE_DEPLOY_COMMIT', env('VAPOR_COMMIT_HASH')))),
    'server' => env('NIGHTWATCH_SERVER', (string) gethostname()),
    'capture_exception_source_code' => (bool) env('NIGHTWATCH_CAPTURE_EXCEPTION_SOURCE_CODE', false),
    'capture_request_payload' => (bool) env('NIGHTWATCH_CAPTURE_REQUEST_PAYLOAD', false),
    'redact_payload_fields' => explode(',', env('NIGHTWATCH_REDACT_PAYLOAD_FIELDS', '_token,password,password_confirmation,email,name,message')),
    'redact_headers' => explode(',', env('NIGHTWATCH_REDACT_HEADERS', 'Authorization,Cookie,Proxy-Authorization,X-XSRF-TOKEN')),

    'sampling' => [
        'requests' => (float) env('NIGHTWATCH_REQUEST_SAMPLE_RATE', 0.1),
        'commands' => (float) env('NIGHTWATCH_COMMAND_SAMPLE_RATE', 1.0),
        'exceptions' => (float) env('NIGHTWATCH_EXCEPTION_SAMPLE_RATE', 1.0),
        'scheduled_tasks' => (float) env('NIGHTWATCH_SCHEDULED_TASK_SAMPLE_RATE', 1.0),
    ],

    'filtering' => [
        'ignore_cache_events' => (bool) env('NIGHTWATCH_IGNORE_CACHE_EVENTS', false),
        'ignore_mail' => (bool) env('NIGHTWATCH_IGNORE_MAIL', false),
        'ignore_notifications' => (bool) env('NIGHTWATCH_IGNORE_NOTIFICATIONS', false),
        'ignore_outgoing_requests' => (bool) env('NIGHTWATCH_IGNORE_OUTGOING_REQUESTS', false),
        'ignore_queries' => (bool) env('NIGHTWATCH_IGNORE_QUERIES', false),
        'log_level' => env('NIGHTWATCH_LOG_LEVEL', env('LOG_LEVEL', 'debug')),
    ],

    'ingest' => [
        'uri' => env('NIGHTWATCH_INGEST_URI', '127.0.0.1:2407'),
        'timeout' => (float) env('NIGHTWATCH_INGEST_TIMEOUT', 0.5),
        'connection_timeout' => (float) env('NIGHTWATCH_INGEST_CONNECTION_TIMEOUT', 0.5),
        'event_buffer' => (int) env('NIGHTWATCH_INGEST_EVENT_BUFFER', 500),
    ],
];
