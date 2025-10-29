<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization;

interface AnonymizationStrategyInterface
{
    /**
     * Applies anonymization logic to the given value.
     *
     * @param mixed $value The original value to anonymize.
     * @return mixed The anonymized result.
     */
    public function anonymize(mixed $value): mixed;
}
