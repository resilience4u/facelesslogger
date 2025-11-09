<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect;


use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\Anonymization\Strategy\MaskEmailStrategy;

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

    public static function default(): self
    {
        $registry = new self();

        $registry->register('email', new MaskEmailStrategy());
        $registry->register('cpf', new \FacelessLogger\Anonymization\Strategy\MaskCpfStrategy());
        $registry->register('password', new \FacelessLogger\Anonymization\Strategy\RedactStrategy());
        $registry->register('token', new \FacelessLogger\Anonymization\Strategy\RedactStrategy());
        $registry->register('authToken', new \FacelessLogger\Anonymization\Strategy\RedactStrategy());
        $registry->register('card_number', new \FacelessLogger\Anonymization\Strategy\MaskCardStrategy());
        $registry->register('ip', new \FacelessLogger\Anonymization\Strategy\MaskIpStrategy());
        $registry->register('mobile', new \FacelessLogger\Anonymization\Strategy\MaskPhoneStrategy());

        return $registry;
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
