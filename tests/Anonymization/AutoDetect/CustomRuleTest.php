<?php

declare(strict_types=1);

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;
use FacelessLogger\Anonymization\AutoDetect\DefaultAutoDetectionRegistry;
use FacelessLogger\Anonymization\Strategy\RedactStrategy;
use PHPUnit\Framework\TestCase;

class CreditCardRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return stripos($key, 'card') !== false
            || preg_match('/\b(?:\d[ -]*?){13,16}\b/', $value);
    }

    public function strategy(): AnonymizationStrategyInterface
    {
        return new RedactStrategy('[REDACTED_CARD]');
    }
}

final class CustomRuleTest extends TestCase
{
    public function testAllowsCustomRules(): void
    {
        $registry = new DefaultAutoDetectionRegistry();

        $rule = new CreditCardRule();

        $registry->register($rule);

        $strategy = $registry->detect('card_number', '1234 5678 9012 3456');
        $this->assertInstanceOf(RedactStrategy::class, $strategy);
        $this->assertSame('[REDACTED_CARD]', $strategy->anonymize('1234 5678 9012 3456'));
    }
}
