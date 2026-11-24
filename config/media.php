<?php

return [
    // Database storage keeps the project's small prepared images durable on serverless hosts.
    'driver' => env('MEDIA_STORAGE_DRIVER', 'local'),
];
