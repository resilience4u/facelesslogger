<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';


use FacelessLogger\Anonymization\AnonymizationProcessor;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Example 5: Auto-Detect Mode
 *
 * Demonstrates automatic detection of sensitive fields and values
 * without any explicit configuration.
 */

$logger = new Logger('faceless');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

// Enable automatic detection mode
$processor = new AnonymizationProcessor();
$logger->pushProcessor($processor);

$logger->info('User submitted registration form', [
    'email' => 'john.doe@example.com',
    'cpf' => '123.456.789-10',
    'password' => 'super_secret_password',
    'authToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1aWQiOiIxMjM0In0.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
    'card_number' => '5555-4444-3333-2222',
    'sessionKey' => 'a8f3e4b9b99d4aab94d3211234567890',
    'address' => 'Av. Brasil 123, Campinas-SP',
    'metadata' => [
        'browser' => 'Chrome',
        'ip' => '192.168.0.12',
        'referral' => 'google',
    ],
]);

$logger->info('User updated preferences', [
    'notifications' => true,
    'language' => 'en',
    'backup_email' => 'backup.account@example.org',
    'mobile' => '+55 11 91234-5678',
]);

$logger->info('Payment processed', [
    'orderId' => 'ORD-9999',
    'card' => '4111-1111-1111-1111',
    'transaction_token' => 'tok_abc123xyz456',
    'status' => 'approved',
]);
