<?php

declare(strict_types=1);


return [
    /*
    |--------------------------------------------------------------------------
    | Default Logging Channel
    |--------------------------------------------------------------------------
    |
    | Defines the name of the Monolog channel used by FacelessLogger.
    |
    */
    'channel' => getenv('FACELESS_LOGGER_CHANNEL') ?? 'faceless',

    /*
    |--------------------------------------------------------------------------
    | Telemetry Integration
    |--------------------------------------------------------------------------
    |
    | Enables OpenTelemetry integration when true.
    |
    */
    'telemetry_enabled' => getenv('FACELESS_TELEMETRY_ENABLED') ?? false,
];
