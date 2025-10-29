<?php

declare(strict_types=1);

use FacelessLogger\Providers\FacelessLoggerServiceProvider;
use FacelessLogger\FacelessLogger;
use PHPUnit\Framework\TestCase;

final class FacelessLoggerServiceProviderTest extends TestCase
{
    public function testRegistersLoggerWithTelemetryDisabled(): void
    {
        $logger = FacelessLoggerServiceProvider::register();
        $this->assertInstanceOf(FacelessLogger::class, $logger);
    }

    public function testRegistersLoggerWithTelemetryEnabled(): void
    {
        $app = [
            'config' => [
                'faceless' => [
                    'telemetry_enabled' => true,
                    'channel' => 'test'
                ]
            ]
        ];

        $logger = FacelessLoggerServiceProvider::register((object)$app);
        $this->assertInstanceOf(FacelessLogger::class, $logger);
    }
}
