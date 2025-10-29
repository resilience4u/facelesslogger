# FacelessLogger

> Inspired by the Japanese *faceless spirit* **Noppera-bō**,  
> **FacelessLogger** is a privacy-first PHP logging library that automatically redacts or anonymizes sensitive information from logs —  
> ensuring **LGPD/GDPR compliance** and protecting your users’ data out of the box.

---

## Key Features

✅ **LGPD-First** – Built to ensure privacy and compliance by default.  
✅ **Automatic Anonymization** – Detects sensitive fields (email, CPF, password, tokens) automatically.  
✅ **Extensible Rules** – Add your own anonymization logic via `AutoDetectionRegistry`.  
✅ **OpenTelemetry Ready** – Emits clean, anonymized logs compatible with the OTel SDK.  
✅ **Framework Agnostic** – Works standalone, in **Laravel**, or **Hyperf**.  
✅ **PSR-3 & PSR-12 Compliant** – Clean, modern, strict-typed PHP 8.3+ code.

---

## Installation

```bash
composer require resilience4u/facelesslogger
```

Minimum requirements:
- PHP 8.3+
- Monolog 3.x
- (Optional) Hyperf 3.x or Laravel 10.x
- OpenTelemetry SDK (optional, for telemetry export)

---

## Usage Examples

### Basic Example (Standalone)

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use FacelessLogger\FacelessLogger;
use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\AutoDetect\DefaultAutoDetectionRegistry;

$logger = FacelessLogger::create('app')
    ->withProcessor(new AnonymizationProcessor(
        autoDetectionRegistry: new DefaultAutoDetectionRegistry()
    ))
    ->withTelemetry();

$logger->info('User logged in', [
    'email' => 'john.doe@example.com',
    'cpf' => '123.456.789-00',
    'password' => 'super_secret',
    'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9....',
]);
```

Output:
```text
[2025-10-29T15:50:40] app.INFO: User logged in 
{"email":"**************le.com","cpf":"[REDACTED]","password":"[REDACTED]","token":"5cd2a65e0e6a…"} []
```

---

### Advanced Example — Custom Rule (Credit Card)

```php
use FacelessLogger\FacelessLogger;
use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRegistry;
use FacelessLogger\Anonymization\Strategy\RedactStrategy;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRuleInterface;

class CreditCardRule implements AutoDetectionRuleInterface
{
    public function matches(string $key, mixed $value): bool
    {
        return is_string($value)
            && (stripos($key, 'card') !== false || preg_match('/\b(?:\d[ -]*?){13,16}\b/', $value));
    }

    public function strategy(): \FacelessLogger\Anonymization\Strategy\AnonymizationStrategyInterface
    {
        return new RedactStrategy('[REDACTED_CARD]');
    }
}

$registry = new AutoDetectionRegistry();
$registry->register(new CreditCardRule());

$logger = FacelessLogger::create('secure-app')
    ->withProcessor(new AnonymizationProcessor(autoDetectionRegistry: $registry))
    ->withTelemetry();

$logger->info('Payment processed', [
    'order_id' => 'ORD-1234',
    'card_number' => '4111 1111 1111 1111',
    'email' => 'john.doe@example.com',
]);
```

Output:
```text
{"card_number":"[REDACTED_CARD]","email":"john.doe@example.com"}
```

---

## Framework Integration

### Laravel / Lumen

Register the service provider in `config/app.php`:

```php
FacelessLogger\Providers\FacelessLoggerServiceProvider::class,
```

Publish the configuration:
```bash
php artisan vendor:publish --tag=faceless-config
```

`config/faceless.php`:
```php
return [
    'channel' => env('FACELESS_LOGGER_CHANNEL', 'faceless'),
    'telemetry_enabled' => env('FACELESS_TELEMETRY_ENABLED', false),
];
```

Usage in any controller:
```php
use FacelessLogger\FacelessLogger;

class UserController
{
    public function store(FacelessLogger $logger)
    {
        $logger->info('New user registered', [
            'email' => 'john.doe@example.com',
            'cpf' => '123.456.789-00',
        ]);
    }
}
```

---

### Hyperf

Your `ConfigProvider.php` automatically registers the dependency:

```php
'dependencies' => [
    FacelessLogger::class => FacelessLogger\Factory\FacelessLoggerFactory::class,
],
```

Use directly via DI:

```php
use FacelessLogger\FacelessLogger;

class UserController
{
    public function index(FacelessLogger $logger)
    {
        $logger->info('User accessed route', [
            'ip' => '192.168.0.10',
            'email' => 'john.doe@example.com'
        ]);
    }
}
```

Publish config:
```bash
php bin/hyperf.php vendor:publish resilience4u/facelesslogger
```

---

## Testing the Library

Run the full test suite:

```bash
composer test
```

Or manually run examples inside the container:

```bash
php examples/5-facade-basic.php
php examples/6-facade-custom-rule.php
```

Expected output:
- All sensitive data anonymized in stdout and telemetry JSON.
- OpenTelemetry exporter emitting clean log records.

---

## Architecture Overview

```text
FacelessLogger
 ├── FacelessLogger.php               # Unified Monolog + OTel Facade
 ├── Anonymization/
 │   ├── AnonymizationProcessor.php   # Core processor (PSR-3/Monolog)
 │   ├── Strategy/                    # MaskStrategy, HashStrategy, RedactStrategy
 │   └── AutoDetect/                  # Registry + Default Rules
 ├── Providers/                       # Laravel Service Provider
 ├── Factory/                         # Hyperf Factory
 └── ConfigProvider.php               # Hyperf ConfigProvider
```

---

## Philosophy

FacelessLogger’s design is based on three key principles:

1. **Privacy by Default** — Logging must never expose user data accidentally.  
2. **Observability-Friendly** — Compliant logs still need to be useful for debugging.  
3. **Extensible by Design** — Rules and strategies are open for extension via the registry API.

---

## License

Licensed under the **Apache 2.0 License**.  
See [`LICENSE`](LICENSE) for details.

---

## ❤️ Acknowledgements

Part of the **Resilience4u** ecosystem —  
a family of open-source tools for resilient, privacy-aware, and observable systems.
