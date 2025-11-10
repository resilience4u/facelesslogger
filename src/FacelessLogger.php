<?php

declare(strict_types=1);

namespace FacelessLogger;

use FacelessLogger\Anonymization\AnonymizationProcessor;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use OpenTelemetry\Contrib\Logs\Monolog\Handler as OTelHandler;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory as OtlpHttpTransportFactoryAlias;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

final class FacelessLogger implements LoggerInterface
{
    private Logger $logger;
    private bool $telemetryEnabled = false;

    public static function create(
        string $channel = 'faceless',
        bool $autoDetect = true,
        ?ProcessorInterface $processor = null
    ): self {
        $instance = new self();

        $instance->logger = new Logger($channel);

        $otelEnabled = getenv('OTEL_ENABLED') ?: getenv('OTEL_EXPORTER_OTLP_ENDPOINT');

        if (!$otelEnabled) {
            $instance->withHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        }

        if ($autoDetect) {
            if ($processor === null && class_exists(AnonymizationProcessor::class)) {
                $processor = new AnonymizationProcessor();
            }
        }

        if ($processor) {
            $instance->withProcessor($processor);
        }

        if ($otelEnabled) {
            $instance->withTelemetry();
        }

        return $instance;
    }

    public function withHandler(HandlerInterface $handler): self
    {
        $this->logger->pushHandler($handler);
        return $this;
    }

    public function withProcessor(ProcessorInterface $processor): self
    {
        $this->logger->pushProcessor($processor);
        return $this;
    }

    public function withTelemetry(): self
    {
        if ($this->isTelemetryEnabled() || !class_exists(OTelHandler::class)) {
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

            $anonymizingHandler = new class ($provider, Level::Debug->value, $this->logger->getProcessors()) extends OTelHandler {
                protected array $processors;
                public function __construct($provider, int $level, array $processors)
                {
                    parent::__construct($provider, $level);
                    $this->processors = $processors;
                }
                public function handle(array|LogRecord $record): bool
                {
                    $logRecord = $record instanceof LogRecord
                        ? $record
                        : new LogRecord(
                            datetime: $record['datetime'] ?? new \DateTimeImmutable(),
                            channel: $record['channel'] ?? 'app',
                            level: Level::fromName($record['level_name'] ?? 'INFO'),
                            message: $record['message'] ?? '',
                            context: $record['context'] ?? [],
                            extra: $record['extra'] ?? []
                        );
                    foreach ($this->processors as $processor) {
                        $logRecord = $processor($logRecord);
                    }
                    return parent::handle($logRecord);
                }
            };

            $this->logger->setHandlers([$anonymizingHandler]);
            $this->telemetryEnabled = true;
        } catch (\Throwable $e) {
            $this->logger->warning('OpenTelemetry integration failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this;
    }

    private function isTelemetryEnabled(): bool
    {
        return $this->telemetryEnabled;
    }

    public function unwrap(): Logger
    {
        return $this->logger;
    }

    public function log($level, $message, array $context = []): void
    {
        $level = match ($level) {
            LogLevel::EMERGENCY => Logger::EMERGENCY,
            LogLevel::ALERT     => Logger::ALERT,
            LogLevel::CRITICAL  => Logger::CRITICAL,
            LogLevel::ERROR     => Logger::ERROR,
            LogLevel::WARNING   => Logger::WARNING,
            LogLevel::NOTICE    => Logger::NOTICE,
            LogLevel::INFO      => Logger::INFO,
            LogLevel::DEBUG     => Logger::DEBUG,
            default              => Logger::INFO,
        };

        $this->logger->addRecord($level, (string) $message, $context);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
