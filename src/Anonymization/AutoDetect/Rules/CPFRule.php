<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect\Rules;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;
use FacelessLogger\Anonymization\Strategy\RedactStrategy;

final class CPFRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        return stripos($key, 'cpf') !== false ||
            (is_string($value) && preg_match('/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/', $value));
    }

    public function strategy(): AnonymizationStrategyInterface
    {
        return new RedactStrategy('[REDACTED]');
    }
}
