<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FacelessLogger\FacelessLogger;
use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\AutoDetect\DefaultAutoDetectionRegistry;

$log = FacelessLogger::create('app')
    ->withProcessor(new AnonymizationProcessor(
        autoDetectionRegistry: new DefaultAutoDetectionRegistry()
    ))
    ->withTelemetry();

$log->info('User logged in', [
    'email' => 'john.doe@example.com',
    'cpf' => '123.456.789-00',
    'password' => 'super_secret',
    'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9....',
]);
