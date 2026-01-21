<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Sampling
    |--------------------------------------------------------------------------
    |
    | To reduce storage and performance overhead, you can configure sampling.
    | A rate of 1.0 captures all requests, 0.5 captures ~50%, etc.
    |
    */

    'sampling' => [
        'rate' => env('INSIGHT_API_SAMPLING_RATE', 1.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Capture Settings
    |--------------------------------------------------------------------------
    |
    | Configure what data should be captured from requests and responses.
    |
    */

    'capture' => [
        'response_body' => env('INSIGHT_API_CAPTURE_RESPONSE_BODY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redaction Rules
    |--------------------------------------------------------------------------
    |
    | Define patterns and field names that should be redacted from captured
    | data. Headers and body fields matching these patterns will have their
    | values replaced with '[REDACTED]'.
    |
    */

    'redaction' => [
        'headers' => [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
        ],

        'body_fields' => [
            'password',
            'password_confirmation',
            'secret',
            'token',
            'api_key',
            'credit_card',
            'card_number',
            'cvv',
            'ssn',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Capture Limits
    |--------------------------------------------------------------------------
    |
    | Set limits on what gets captured to avoid storing excessively large
    | payloads.
    |
    */
    'limits' => [
        'max_body_size' => env('INSIGHT_API_MAX_BODY_SIZE', 64 * 1024), // 64KB
        'max_response_size' => env('INSIGHT_API_MAX_RESPONSE_SIZE', 64 * 1024), // 64KB
    ],
];
