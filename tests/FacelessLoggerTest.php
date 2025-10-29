<?php

declare(strict_types=1);

use FacelessLogger\FacelessLogger;
use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\AutoDetect\DefaultAutoDetectionRegistry;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use PHPUnit\Framework\TestCase;

final class FacelessLoggerTest extends TestCase
{
    public function testCreatesLoggerInstance(): void
    {
        $logger = FacelessLogger::create('test-channel');
        $this->assertInstanceOf(FacelessLogger::class, $logger);
    }

    public function testLogsInformationWithAnonymization(): void
    {
        $logger = FacelessLogger::create('test');

        $registry = new DefaultAutoDetectionRegistry();
        $processor = new AnonymizationProcessor(autoDetectionRegistry: $registry);
        $logger->withProcessor($processor);

        $testHandler = new TestHandler(Level::Info);
        $logger->withHandler($testHandler);

        $logger->info('User login', [
            'email' => 'john.doe@example.com',
            'password' => 'super_secret',
        ]);

        $records = $testHandler->getRecords();
        $this->assertNotEmpty($records, 'Nenhum log foi registrado.');

        $context = $records[0]['context'];

        $this->assertNotSame('john.doe@example.com', $context['email']);
        $this->assertNotSame('super_secret', $context['password']);
        $this->assertStringContainsString('*', $context['email']);
        $this->assertSame('[REDACTED]', $context['password']);
    }
}
