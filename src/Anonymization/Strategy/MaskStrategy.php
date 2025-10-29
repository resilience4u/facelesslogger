<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\Strategy;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;

final class MaskStrategy implements AnonymizationStrategyInterface
{
    public function __construct(
        private readonly string $maskChar = '*',
        private readonly int $keepLast = 0
    ) {
        if ($this->keepLast < 0) {
            throw new \InvalidArgumentException('keepLast must be >= 0');
        }

        if ($this->maskChar === '') {
            throw new \InvalidArgumentException('maskChar cannot be empty');
        }
    }

    public function anonymize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (\is_string($value) || (\is_object($value) && method_exists($value, '__toString'))) {
            $str = (string) $value;
            $len = function_exists('mb_strlen') ? mb_strlen($str, 'UTF-8') : strlen($str);

            if ($len === 0) {
                return $str;
            }

            $keep = min($this->keepLast, $len);
            $maskLen = $len - $keep;

            $mask = str_repeat($this->maskChar, $maskLen);
            $suffix = $keep > 0
                ? (function_exists('mb_substr') ? mb_substr($str, -$keep, null, 'UTF-8') : substr($str, -$keep))
                : '';

            return $mask . $suffix;
        }

        if (\is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->anonymize($v);
            }
            return $out;
        }

        return $this->anonymize((string) $value);
    }
}
