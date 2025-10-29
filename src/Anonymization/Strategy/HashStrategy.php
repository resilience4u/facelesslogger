<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\Strategy;

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;

final class HashStrategy implements AnonymizationStrategyInterface
{
    public function __construct(
        private readonly string $algorithm = 'sha256',
        private readonly string $saltPrefix = '',
        private readonly string $saltSuffix = '',
        private readonly bool $rawOutput = false
    ) {
        if (!in_array($this->algorithm, hash_algos(), true)) {
            throw new \InvalidArgumentException(sprintf('Hash algorithm "%s" is not supported', $this->algorithm));
        }
    }

    public function anonymize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (\is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->anonymize($v);
            }
            return $out;
        }

        return hash(
            $this->algorithm,
            $this->saltPrefix . (string) $value . $this->saltSuffix,
            $this->rawOutput
        );
    }
}
