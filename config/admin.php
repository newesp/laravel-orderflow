<?php
return [
    'demo' => [
        'enabled' => filter_var(env('DEMO_ADMIN_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'email' => env('DEMO_ADMIN_EMAIL', ''),
        'password' => env('DEMO_ADMIN_PASSWORD', ''),
    ],
];
