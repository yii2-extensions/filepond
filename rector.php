<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ArrowFunction\ArrowFunctionDelegatingCallToFirstClassCallableRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/vendor/php-forge/coding-standard/src/rector-83.php');

    $rectorConfig->importNames(true, false);

    $rectorConfig->paths(
        [
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ],
    );

    $rectorConfig->skip(
        [
            // internal-mocker serializes the closure source into the generated mock functions, so the delegating
            // arrow functions must stay closures instead of first-class callables.
            ArrowFunctionDelegatingCallToFirstClassCallableRector::class => [
                __DIR__ . '/tests/support/MockerExtension.php',
            ],
        ],
    );
};
