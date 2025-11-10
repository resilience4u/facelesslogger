<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\Strategy;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;

/**
 * Masks credit card numbers while preserving the first and last 4 digits.
 * Supports common card formats with spaces or hyphens.
 *
 * Example:
 *  4111 1111 1111 1111 → 4111 **** **** 1111
 */
final class MaskCardStrategy implements AnonymizationStrategyInterface
{
    public function anonymize(mixed $value): string
    {
        if (!is_string($value)) {
            return (string) $value;
        }

        $normalized = preg_replace('/[\s-]/', '', $value ?? '');

        if (!preg_match('/^\d{13,19}$/', $normalized)) {
            return $value;
        }

        $masked = substr($normalized, 0, 4)
            . str_repeat('*', strlen($normalized) - 8)
            . substr($normalized, -4);

        return trim(chunk_split($masked, 4, ' '));
    }
}
