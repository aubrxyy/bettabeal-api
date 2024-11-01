<?php

return [
    'paths' => ['api/*'], // Adjust as needed
    'allowed_methods' => ['*'], // Allow all methods
    'allowed_origins' => ['*'], // Allow all origins or specify your frontend URL
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
