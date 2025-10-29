<?php

declare(strict_types=1);

namespace FacelessLogger;

use FacelessLogger\Anonymization\AnonymizationProcessor;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Processor\ProcessorInterface;
use OpenTelemetry\Contrib\Logs\Monolog\Handler as OTelHandler;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use Stringable;

/**
 * FacelessLogger
 *
 * A developer-friendly facade around Monolog, preconfigured with LGPD-first anonymization.
 * Ideal for Laravel, Hyperf or standalone apps that need privacy-safe logging.
 */
final class FacelessLogger
{
    private Logger $logger;
    private bool $telemetryEnabled = false;

    public static function create(
        string $channel = 'faceless',
        bool $autoDetect = true,
        ?ProcessorInterface $processor = null
    ): self {
        $instance = new self();

        // ✅ Create single Logger instance early
        $instance->logger = new Logger($channel);

        // ✅ Default handler
        $instance->withHandler(new StreamHandler('php://stdout', Logger::DEBUG));

        $processor ??= new AnonymizationProcessor();
        $instance->withProcessor($processor);

        return $instance;
    }

    public function withHandler(HandlerInterface $handler): self
    {
        // No new logger — always operate on the same instance
        $this->logger->pushHandler($handler);
        return $this;
    }

    public function withProcessor(ProcessorInterface $processor): self
    {
        $this->logger->pushProcessor($processor);
        return $this;
    }

    /**
     * Enables OpenTelemetry integration safely.
     * Uses a StreamTransport for ConsoleExporter as fallback.
     */
    public function withTelemetry(): self
    {
        if ($this->isTelemetryEnabled()) {
            return $this;
        }

        if (!class_exists(OTelHandler::class)) {
            return $this;
        }

        try {
            $transportFactory = new StreamTransportFactory();
            $transport = $transportFactory->create('php://stdout', 'application/json');

            $exporter   = new ConsoleExporter($transport);
            $processor  = new SimpleLogRecordProcessor($exporter);

            $attributesFactory = new AttributesFactory();
            $scopeFactory      = new InstrumentationScopeFactory($attributesFactory);

            $provider = new LoggerProvider($processor, $scopeFactory);

            // ✅ Custom wrapper handler to anonymize before sending to OTel
            $anonymizingHandler = new class($provider, Logger::DEBUG, $this->logger->getProcessors()) extends OTelHandler {
                protected array $processors;

                public function __construct($provider, int $level, array $processors)
                {
                    parent::__construct($provider, $level);
                    $this->processors = $processors;
                }

                public function handle(array|\Monolog\LogRecord $record): bool
                {
                    // Ensure record is a LogRecord instance
                    $logRecord = $record instanceof \Monolog\LogRecord
                        ? $record
                        : new \Monolog\LogRecord(
                            datetime: $record['datetime'] ?? new \DateTimeImmutable(),
                            channel: $record['channel'] ?? 'app',
                            level: \Monolog\Level::fromName($record['level_name'] ?? 'INFO'),
                            message: $record['message'] ?? '',
                            context: $record['context'] ?? [],
                            extra: $record['extra'] ?? []
                        );

                    // Apply processors (AnonymizationProcessor, etc.)
                    foreach ($this->processors as $processor) {
                        $logRecord = $processor($logRecord);
                    }

                    // Send processed record to the OTel handler
                    return parent::handle($logRecord);
                }
            };

            // ✅ Human-readable handler
            $consoleHandler = new StreamHandler('php://stdout', Logger::INFO);

            $this->withHandler($consoleHandler);
            $this->withHandler($anonymizingHandler);

            $this->telemetryEnabled = true;
        } catch (\Throwable $e) {
            $this->logger->warning('OpenTelemetry integration failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this;
    }



    /**
     * Whether telemetry is currently active.
     */
    private function isTelemetryEnabled(): bool
    {
        return $this->telemetryEnabled;
    }

    public function unwrap(): Logger
    {
        return $this->logger;
    }

    // Proxy convenience methods ---------------------------------------

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }
}
