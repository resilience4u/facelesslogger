<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect;


use FacelessLogger\Anonymization\AnonymizationStrategyInterface;

/**
 * Registry for auto-detection rules.
 * Allows dynamic registration and resolution of sensitive field rules.
 */
class AutoDetectionRegistry
{
    /** @var AutoDetectionRuleInterface[] */
    private array $rules = [];

    public function register(AutoDetectionRuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * Returns the first strategy that matches the provided key/value.
     */
    public function detect(string $key, mixed $value): ?AnonymizationStrategyInterface
    {
        foreach ($this->rules as $rule) {
            if ($rule->matches($key, $value)) {
                return $rule->strategy();
            }
        }

        return null;
    }
}
