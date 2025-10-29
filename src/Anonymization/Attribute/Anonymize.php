<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization\Attribute;

use Attribute;
use FacelessLogger\Anonymization\AnonymizationStrategyInterface;

/**
 * Attribute to mark public object properties for anonymization.
 *
 * Example:
 *  #[Anonymize(new MaskStrategy('*', 2))]
 *  public string $email;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Anonymize
{
    public function __construct(
        public readonly AnonymizationStrategyInterface $strategy
    ) {
    }
}
