<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\Strategy;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;

/**
 * Masks IPv4 and IPv6 addresses while preserving partial structure.
 *
 * Examples:
 *   - 192.168.1.100 → 192.168.***.***
 *   - 2804:14c:123:abc::1 → 2804:14c:***:***::***
 */
final class MaskIpStrategy implements AnonymizationStrategyInterface
{
    public function anonymize(mixed $value): string
    {
        if (!is_string($value)) {
            return (string) $value;
        }

        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $segments = explode('.', $value);
            return sprintf('%s.%s.***.***', $segments[0], $segments[1]);
        }

        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $segments = explode(':', $value);
            $masked = array_map(
                fn ($i, $part) => $i < 2 ? $part : '***',
                array_keys($segments),
                $segments
            );
            return implode(':', $masked);
        }

        return $value;
    }
}
