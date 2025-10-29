<?php

declare(strict_types=1);

namespace FacelessLogger;

use FacelessLogger\Factory\FacelessLoggerFactory;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                FacelessLogger::class => FacelessLoggerFactory::class,
            ],
            'commands' => [],
            'annotations' => [],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file for FacelessLogger.',
                    'source' => __DIR__ . '/Config/faceless.php',
                    'destination' => (defined('BASE_PATH') ? BASE_PATH : getcwd()) . '/config/autoload/faceless.php',
                ],
            ],
        ];
    }
}
