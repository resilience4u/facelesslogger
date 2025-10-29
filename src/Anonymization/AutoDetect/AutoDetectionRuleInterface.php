<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect;


use FacelessLogger\Anonymization\AnonymizationStrategyInterface;

/**
 * Defines a contract for an automatic detection rule.
 * Each rule determines whether a context key/value should be anonymized
 * and which strategy to apply.
 */
interface AutoDetectionRuleInterface
{
    /**
     * Determines if this rule matches a given field.
     *
     * @param string $key
     * @param mixed $value
     */
    public function matches(string $key, mixed $value): bool;

    /**
     * Returns the anonymization strategy to be applied.
     */
    public function strategy(): AnonymizationStrategyInterface;
}
