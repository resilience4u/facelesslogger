<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect\Rules;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;

final class TokenRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        return stripos($key, 'token') !== false ||
            (is_string($value) && preg_match('/^[A-F0-9]{32,}$/i', $value));
    }

    public function strategy(): AnonymizationStrategyInterface
    {
        return new class implements AnonymizationStrategyInterface {
            public function anonymize(mixed $value): mixed
            {
                $hash = hash('sha256', (string) $value);
                return substr($hash, 0, 12) . '…';
            }
        };
    }
}
