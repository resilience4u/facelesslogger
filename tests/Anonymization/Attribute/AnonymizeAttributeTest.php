<?php

declare(strict_types=1);

use FacelessLogger\Anonymization\Attribute\Anonymize;
use FacelessLogger\Anonymization\Strategy\RedactStrategy;
use PHPUnit\Framework\TestCase;

final class AnonymizeAttributeTest extends TestCase
{
    public function testStoresStrategyInstance(): void
    {
        $strategy = new RedactStrategy('[MASKED]');
        $attribute = new Anonymize($strategy);

        $this->assertSame($strategy, $attribute->strategy);
    }
}
