<?php

declare(strict_types=1);

use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\Strategy\RedactStrategy;
use PHPUnit\Framework\TestCase;
use Monolog\LogRecord;
use Monolog\Level;

final class AnonymizationProcessorTest extends TestCase
{
    public function testKeyBasedAnonymization(): void
    {
        $processor = new AnonymizationProcessor([
            'password' => new RedactStrategy('[REDACTED]')
        ]);

        $record = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'User login',
            context: ['password' => 'super_secret']
        );

        $processed = $processor($record);
        $this->assertSame('[REDACTED]', $processed->context['password']);
    }
}
