<?php

declare(strict_types=1);

namespace FacelessLogger\Anonymization;

use FacelessLogger\Anonymization\Attribute\Anonymize;
use FacelessLogger\Anonymization\AutoDetect\AutoDetectionRegistry;
use Monolog\Processor\ProcessorInterface;
use Monolog\LogRecord;
use ReflectionClass;
use ReflectionProperty;

/**
 * Monolog Processor designed for LGPD-first anonymization.
 *
 * Responsibilities:
 *  - Apply explicit key or regex anonymization strategies.
 *  - Process #[Anonymize] attributes on public object properties.
 *  - Delegate sensitive-field autodetection to a registry (if provided).
 */
final class AnonymizationProcessor implements ProcessorInterface
{
    /**
     * @param array<string, AnonymizationStrategyInterface> $keyStrategies
     * @param array<string, AnonymizationStrategyInterface> $regexStrategies
     */
    public function __construct(
        private array $keyStrategies = [],
        private array $regexStrategies = [],
        private ?AutoDetectionRegistry $autoDetectionRegistry = null,
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $this->processContext($record->context);

        $message = $record->message;
        if (!empty($this->regexStrategies) && is_string($message)) {
            $message = $this->applyRegexStrategies($message);
        }

        return $record->with(context: $context, message: $message);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function processContext(array $context): array
    {
        $out = [];

        foreach ($context as $key => $value) {
            $v = $value;

            // 1) Key-based anonymization
            if (isset($this->keyStrategies[$key])) {
                $v = $this->applyStrategyRecursive($v, $this->keyStrategies[$key]);
            }

            // 2) Attribute-based anonymization for objects
            if (\is_object($v)) {
                $v = $this->anonymizeObject($v);
            } elseif (\is_array($v)) {
                $v = $this->processArrayObjects($v);
            }

            // 3) Regex-based fallback
            if (!empty($this->regexStrategies)) {
                $v = $this->applyRegexIfStringRecursive($v);
            }

            // 4) Auto-detection via registry (optional)
            if ($this->autoDetectionRegistry) {
                $strategy = $this->autoDetectionRegistry->detect($key, $v);
                if ($strategy) {
                    $v = $strategy->anonymize($v);
                }
            }

            $out[$key] = $v;
        }

        return $out;
    }

    private function processArrayObjects(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (\is_object($v)) {
                $out[$k] = $this->anonymizeObject($v);
            } elseif (\is_array($v)) {
                $out[$k] = $this->processArrayObjects($v);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    private function anonymizeObject(object $obj): object
    {
        $clone = clone $obj;
        $ref   = new ReflectionClass($clone);

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            foreach ($prop->getAttributes(Anonymize::class) as $attr) {
                /** @var Anonymize $instance */
                $instance = $attr->newInstance();
                $value    = $prop->getValue($clone);
                $prop->setValue($clone, $instance->strategy->anonymize($value));
            }
        }

        return $clone;
    }

    private function applyStrategyRecursive(mixed $value, AnonymizationStrategyInterface $strategy): mixed
    {
        if (\is_array($value)) {
            return array_map(fn($v) => $this->applyStrategyRecursive($v, $strategy), $value);
        }

        if (\is_object($value)) {
            return $this->anonymizeObject($value);
        }

        return $strategy->anonymize($value);
    }

    private function applyRegexStrategies(string $text): string
    {
        foreach ($this->regexStrategies as $pattern => $strategy) {
            $text = preg_replace_callback(
                $pattern,
                fn(array $matches) => $strategy->anonymize($matches[0]),
                $text
            ) ?? $text;
        }

        return $text;
    }

    private function applyRegexIfStringRecursive(mixed $value): mixed
    {
        if (\is_string($value)) {
            return $this->applyRegexStrategies($value);
        }

        if (\is_array($value)) {
            return array_map(fn($v) => $this->applyRegexIfStringRecursive($v), $value);
        }

        if (\is_object($value)) {
            return $this->anonymizeObject($value);
        }

        return $value;
    }
}
