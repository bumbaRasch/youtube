<?php

return [
    'rate_limit_per_minute' => env('EXTRACTOR_RATE_LIMIT', 10),
    'max_duration_minutes' => env('EXTRACTOR_MAX_DURATION_MINUTES', 30),
    'audit_log_retention_days' => env('EXTRACTOR_AUDIT_RETENTION_DAYS', 30),
    'process_timeout' => env('EXTRACTOR_PROCESS_TIMEOUT', 120),
];
