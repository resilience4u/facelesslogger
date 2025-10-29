<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FacelessLogger\Anonymization\AnonymizationStrategyInterface;
use FacelessLogger\FacelessLogger;
use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRegistry;
use FacelessLogger\Anonymization\Strategy\RedactStrategy;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;

/**
 * Custom rule: detect and redact credit card numbers (simple pattern)
 */
class CreditCardRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return stripos($key, 'card') !== false
            || preg_match('/\b(?:\d[ -]*?){13,16}\b/', $value);
    }

    public function strategy(): AnonymizationStrategyInterface
    {
        return new RedactStrategy('[REDACTED_CARD]');
    }
}

$registry = new AutoDetectionRegistry();
$registry->register(new CreditCardRule());

$log = FacelessLogger::create('secure-app')
    ->withProcessor(new AnonymizationProcessor(
        autoDetectionRegistry: $registry
    ))
    ->withTelemetry();

$log->info('Payment processed', [
    'order_id' => 'ORD-1234',
    'card_number' => '4111 1111 1111 1111',
    'card_holder' => 'John Doe',
    'email' => 'john.doe@example.com'
]);
