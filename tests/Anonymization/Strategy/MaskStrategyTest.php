<?php

declare(strict_types=1);

use FacelessLogger\Anonymization\Strategy\MaskStrategy;
use PHPUnit\Framework\TestCase;

final class MaskStrategyTest extends TestCase
{
    public function testMasksStringKeepingLastCharacters(): void
    {
        $strategy = new MaskStrategy('*', 5);

        $result = $strategy->anonymize('john.doe@example.com');
        $this->assertSame('***************e.com', $result);
    }

    public function testHandlesShortStringGracefully(): void
    {
        $strategy = new MaskStrategy('*', 10);

        $result = $strategy->anonymize('abc');
        $this->assertSame('abc', $result);
    }

    public function testHandlesNonStringValues(): void
    {
        $strategy = new MaskStrategy();
        $this->assertSame(123, $strategy->anonymize(123));
    }
}
