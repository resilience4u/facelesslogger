<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect\Rules;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;
use FacelessLogger\Anonymization\Strategy\RedactStrategy;

final class PasswordRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        return stripos($key, 'password') !== false;
    }

    public function strategy(): AnonymizationStrategyInterface
    {
        return new RedactStrategy('[REDACTED]');
    }
}
