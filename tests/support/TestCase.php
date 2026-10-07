<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\support;

use PHPForge\Support\DirectoryCleaner;
use ReflectionClass;
use Yii;
use yii\base\{InvalidConfigException, Widget};
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\web\{Application, View};
use yii2\extensions\filepond\Bootstrap;
use yii2\extensions\filepond\tests\support\stub\MockerFunctions;

use function basename;
use function dirname;
use function is_dir;
use function mkdir;

/**
 * Base class for the extension test suite.
 *
 * Provides a web application factory with an asset manager publishing into `runtime`, the extension bootstrap, and
 * cleanup of published assets and mocked functions after each test.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const string RUNTIME_PATH = __DIR__ . '/../../runtime/tests';

    /**
     * Creates a web application with the extension bootstrap and an isolated asset manager.
     *
     * @param array<string, mixed> $config Configuration merged over the defaults.
     *
     * @throws InvalidConfigException if the configuration is invalid.
     */
    protected function mockWebApplication(array $config = []): Application
    {
        $assetPath = self::RUNTIME_PATH . '/assets';

        if (!is_dir($assetPath)) {
            mkdir($assetPath, 0o777, true);
        }

        return new Application(
            ArrayHelper::merge(
                [
                    'id' => 'test-app',
                    'basePath' => dirname(__DIR__, 2),
                    'bootstrap' => [Bootstrap::class],
                    'components' => [
                        'assetManager' => [
                            'basePath' => $assetPath,
                            'baseUrl' => '/assets',
                            'hashCallback' => basename(...),
                        ],
                        'request' => [
                            'cookieValidationKey' => 'test-cookie-validation-key',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                    ],
                    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
                ],
                $config,
            ),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        MockerFunctions::reset();

        (new ReflectionClass(Widget::class))->setStaticPropertyValue('counter', 0);
    }

    protected function tearDown(): void
    {
        MockerFunctions::reset();

        (new ReflectionClass(Yii::class))->setStaticPropertyValue('app', null);

        Yii::$container = new Container();

        if (is_dir(self::RUNTIME_PATH)) {
            DirectoryCleaner::clean(self::RUNTIME_PATH);
        }

        parent::tearDown();
    }

    /**
     * Creates a view bound to the current application.
     */
    protected function view(): View
    {
        return new View();
    }
}
