<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\support;

use GdImage;
use PHPUnit\Event\Test\{PreparationStarted, PreparationStartedSubscriber};
use PHPUnit\Event\TestSuite\{Started, StartedSubscriber};
use PHPUnit\Runner\Extension\{Extension, Facade, ParameterCollection};
use PHPUnit\TextUI\Configuration\Configuration;
use Xepozz\InternalMocker\{Mocker, MockerState};
use yii2\extensions\filepond\tests\support\stub\MockerFunctions;

/**
 * PHPUnit extension that registers internal-function mocks for the `file` namespace.
 */
final class MockerExtension implements Extension
{
    /**
     * Registers event subscribers that initialize and reset mock state.
     */
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class implements StartedSubscriber {
                public function notify(Started $event): void
                {
                    MockerExtension::load();
                }
            },
            new class implements PreparationStartedSubscriber {
                public function notify(PreparationStarted $event): void
                {
                    MockerState::resetState();
                }
            },
        );
    }

    /**
     * Loads configured function mocks and snapshots their initial state.
     */
    public static function load(): void
    {
        $namespace = 'yii2\extensions\filepond\file';

        $mocks = [
            [
                'namespace' => $namespace,
                'name' => 'exif_read_data',
                'function' => static fn(
                    $file,
                    string|null $required_sections = null,
                    bool $as_arrays = false,
                    bool $read_thumbnail = false,
                ): array|false => MockerFunctions::exif_read_data($file),
            ],
            [
                'namespace' => $namespace,
                'name' => 'extension_loaded',
                'function' => static fn(string $extension): bool => MockerFunctions::extension_loaded($extension),
            ],
            [
                'namespace' => $namespace,
                'name' => 'file_put_contents',
                'function' => static fn(
                    string $filename,
                    mixed $data,
                    int $flags = 0,
                    $context = null,
                ): int|false => MockerFunctions::file_put_contents($filename, $data, $flags),
            ],
            [
                'namespace' => $namespace,
                'name' => 'function_exists',
                'function' => static fn(string $function): bool => MockerFunctions::function_exists($function),
            ],
            [
                'namespace' => $namespace,
                'name' => 'imagecrop',
                'function' => static fn(GdImage $image, array $rectangle): GdImage|false => MockerFunctions::imagecrop(
                    $image,
                    $rectangle,
                ),
            ],
            [
                'namespace' => $namespace,
                'name' => 'imagerotate',
                'function' => static fn(
                    GdImage $image,
                    float $angle,
                    int $background_color,
                    bool $ignore_transparent = false,
                ): GdImage|false => MockerFunctions::imagerotate($image, $angle, $background_color),
            ],
            [
                'namespace' => $namespace,
                'name' => 'imagewebp',
                'function' => static fn(GdImage $image, $file = null, int $quality = -1): bool => MockerFunctions::imagewebp(
                    $image,
                    $file,
                    $quality,
                ),
            ],
            [
                'namespace' => $namespace,
                'name' => 'is_writable',
                'function' => static fn(string $filename): bool => MockerFunctions::is_writable($filename),
            ],
        ];

        $mocker = new Mocker(stubPath: __DIR__ . '/internal-mocker-stubs.php');

        $mocker->load($mocks);

        MockerState::saveState();
    }
}
