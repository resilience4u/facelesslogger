<?php

declare(strict_types=1);

namespace FacelessLogger\Factory;

use FacelessLogger\FacelessLogger;
use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\AutoDetect\DefaultAutoDetectionRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class FacelessLoggerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FacelessLogger
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $facelessConfig = $config['faceless'] ?? [];

        $enableTelemetry = (bool)($facelessConfig['telemetry_enabled'] ?? false);
        $channel = $facelessConfig['channel'] ?? 'faceless';

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
