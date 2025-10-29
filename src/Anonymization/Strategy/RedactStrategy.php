<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\Strategy;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;

final class RedactStrategy implements AnonymizationStrategyInterface
{
    public function __construct(
        private readonly string $placeholder = '[REDACTED]'
    ) {
        if ($this->placeholder === '') {
            throw new \InvalidArgumentException('placeholder cannot be empty');
        }
    }

    public function anonymize(mixed $value): mixed
    {
        if (\is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->anonymize($v);
            }
            return $out;
        }

        return $this->placeholder;
    }
}
