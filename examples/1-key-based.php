<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\Strategy\MaskStrategy;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// ------------------------------
// Example 1: Key-Based Strategy
// ------------------------------

$logger = new Logger('faceless');
$logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));

$processor = new AnonymizationProcessor(
    keyStrategies: [
        'email' => new MaskStrategy('*', 3),
        'phone' => new MaskStrategy('#', 2),
    ]
);

$logger->pushProcessor($processor);

$logger->info('User signed up', [
    'email' => 'john.doe@example.com',
    'phone' => '+55 11 98765-4321',
    'city'  => 'Campinas',
]);
