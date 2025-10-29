<?php

declare(strict_types=1);

namespace FacelessLogger\Providers;

use FacelessLogger\FacelessLogger;
use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\AutoDetect\DefaultAutoDetectionRegistry;

final class FacelessLoggerServiceProvider
{
    public static function register(mixed $app = null): FacelessLogger
    {
        $config = [];
        if (is_object($app) && method_exists($app, 'make') && method_exists($app, 'config')) {
            $config = $app['config']['faceless'] ?? [];
        }

        $enableTelemetry = (bool)($config['telemetry_enabled'] ?? false);
        $channel = $config['channel'] ?? 'faceless';

        $registry = new DefaultAutoDetectionRegistry();
        $processor = new AnonymizationProcessor(autoDetectionRegistry: $registry);

        $logger = FacelessLogger::create($channel)
            ->withProcessor($processor);

        if ($enableTelemetry) {
            $logger->withTelemetry();
        }

        return $logger;
    }
}
