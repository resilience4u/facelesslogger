<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\Strategy\RedactStrategy;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// ------------------------------
// Example 3: Regex-Based Strategy
// ------------------------------
$logger = new Logger('faceless');
$logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));

$regexes = [
    '/\b\d{3}\.\d{3}\.\d{3}\-\d{2}\b/' => new RedactStrategy('[CPF_REDACTED]'),
    '/\b\d{4}-\d{4}-\d{4}-\d{4}\b/'    => new RedactStrategy('[CARD_REDACTED]'),
];

$processor = new AnonymizationProcessor(regexStrategies: $regexes);

$logger->pushProcessor($processor);

$logger->info(
    'Payment processed for user 123.456.789-10 with card 5555-4444-3333-2222'
);
