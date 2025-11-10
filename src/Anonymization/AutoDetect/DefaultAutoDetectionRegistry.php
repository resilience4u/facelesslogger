<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\AutoDetect;

use FacelessLogger\Anonymization\AutoDetect\Rules\{CreditCardRule, EmailRule, CPFRule, PasswordRule, TokenRule};

/**
 * Default auto-detection registry used when the user
 * doesn't provide a custom one.
 *
 * Provides sane defaults for LGPD-sensitive fields.
 */
class DefaultAutoDetectionRegistry extends AutoDetectionRegistry
{
    public function __construct()
    {
        // Register built-in LGPD-sensitive field rules
        $this->register(new EmailRule());
        $this->register(new CPFRule());
        $this->register(new PasswordRule());
        $this->register(new TokenRule());
        $this->register(new CreditCardRule());
    }
}
