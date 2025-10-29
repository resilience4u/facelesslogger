<?php

declare(strict_types=1);

use FacelessLogger\Anonymization\Strategy\RedactStrategy;
use PHPUnit\Framework\TestCase;

final class RedactStrategyTest extends TestCase
{
    public function testReplacesWithPlaceholder(): void
    {
        $strategy = new RedactStrategy('[REDACTED]');
        $this->assertSame('[REDACTED]', $strategy->anonymize('sensitive data'));
    }

    public function testHandlesNullGracefully(): void
    {
        $strategy = new RedactStrategy('[REMOVED]');
        $this->assertSame('[REMOVED]', $strategy->anonymize(null));
    }
}
