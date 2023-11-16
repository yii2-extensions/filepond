<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests;

use Yii;
use yii\di\Container;
use yii\i18n\PhpMessageSource;
use yii\web\Application;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    protected function mockApplication(): void
    {
        new Application(
            [
                'id' => 'testapp',
                'aliases' => [
                    '@bower' => dirname(__DIR__) . '/node_modules',
                    '@npm' => dirname(__DIR__) . '/node_modules',
                ],
                'basePath' => __DIR__,
                'vendorPath' => dirname(__DIR__) . '/vendor',
                'components' => [
                    'assetManager' => [
                        'basePath' => __DIR__ . '/Support/runtime',
                        'baseUrl' => '/',
                    ],
                    'i18n' => [
                        'translations' => [
                            'yii.filepond' => [
                                'class' => PhpMessageSource::class,
                            ],
                        ],
                    ],
                    'request' => [
                        'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                        'scriptFile' => __DIR__ . '/index.php',
                        'scriptUrl' => '/index.php',
                    ],
                ],
            ],
        );
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }
}
