<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect\Rules;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;
use FacelessLogger\Anonymization\Strategy\MaskCardStrategy;

final class CreditCardRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        $keyMatch = preg_match('/card|cc|credit/i', $key) === 1;

        $valueMatch = false;
        if (is_string($value)) {
            $normalized = preg_replace('/[\s-]/', '', $value);
            $valueMatch = preg_match('/^\d{13,19}$/', $normalized) === 1;
        }

        return $keyMatch || $valueMatch;
    }

    public function strategy(): AnonymizationStrategyInterface
    {
        return new MaskCardStrategy();
    }
}
