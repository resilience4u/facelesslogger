<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect\Rules;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;
use FacelessLogger\Anonymization\Strategy\MaskStrategy;

final class EmailRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        return stripos($key, 'email') !== false ||
            (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL));
    }

    public function strategy(): AnonymizationStrategyInterface
    {
        return new MaskStrategy('*', 6);
    }
}
