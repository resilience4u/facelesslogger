<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect\Rules;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;
use FacelessLogger\Anonymization\Strategy\MaskIpStrategy;

final class IpAddressRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        $keyMatch = stripos($key, 'ip') !== false;

        $valueMatch = false;
        if (is_string($value)) {
            $valueMatch = filter_var($value, FILTER_VALIDATE_IP) !== false;
        }

        return $keyMatch || $valueMatch;
    }

    public function strategy(): AnonymizationStrategyInterface
    {
        return new MaskIpStrategy();
    }
}
