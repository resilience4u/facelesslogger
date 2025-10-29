<?php

declare(strict_types=1);

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\AutoDetect\DefaultAutoDetectionRegistry;
use PHPUnit\Framework\TestCase;

final class DefaultAutoDetectionRegistryTest extends TestCase
{
    private DefaultAutoDetectionRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new DefaultAutoDetectionRegistry();
    }

    public function testDetectsEmail(): void
    {
        $strategy = $this->registry->detect('email', 'john.doe@example.com');
        $this->assertInstanceOf(AnonymizationStrategyInterface::class, $strategy);
        $this->assertStringContainsString('*', $strategy->anonymize('john.doe@example.com'));
    }

    public function testDetectsCpf(): void
    {
        $strategy = $this->registry->detect('cpf', '123.456.789-00');
        $this->assertInstanceOf(AnonymizationStrategyInterface::class, $strategy);
        $this->assertSame('[REDACTED]', $strategy->anonymize('123.456.789-00'));
    }

    public function testDetectsPassword(): void
    {
        $strategy = $this->registry->detect('password', 'secret');
        $this->assertInstanceOf(AnonymizationStrategyInterface::class, $strategy);
        $this->assertSame('[REDACTED]', $strategy->anonymize('secret'));
    }

    public function testDetectsToken(): void
    {
        $strategy = $this->registry->detect('token', 'abcdef1234567890abcdef1234567890');
        $this->assertInstanceOf(AnonymizationStrategyInterface::class, $strategy);
        $anonymized = $strategy->anonymize('abcdef1234567890abcdef1234567890');
        $this->assertMatchesRegularExpression('/[a-f0-9]{12}…/', $anonymized);
    }

    public function testReturnsNullForUnmatchedKey(): void
    {
        $strategy = $this->registry->detect('random_field', 'some value');
        $this->assertNull($strategy);
    }
}
