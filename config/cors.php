<?php

return [
    'paths' => ['api/*', 'login', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*'))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition', 'Idempotency-Replayed'],

    'max_age' => 600,

    'supports_credentials' => false,
];
