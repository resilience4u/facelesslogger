<?php

declare(strict_types=1);

use FacelessLogger\Anonymization\Strategy\HashStrategy;
use PHPUnit\Framework\TestCase;

final class HashStrategyTest extends TestCase
{
    public function testAppliesSha256HashByDefault(): void
    {
        $strategy = new HashStrategy();
        $result = $strategy->anonymize('secret');

        $this->assertSame(hash('sha256', 'secret'), $result);
    }

    public function testSupportsDifferentAlgorithms(): void
    {
        $strategy = new HashStrategy('md5');
        $this->assertSame(md5('secret'), $strategy->anonymize('secret'));
    }
}
