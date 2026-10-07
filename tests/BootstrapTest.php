<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\helpers\ReplaceArrayValue;
use yii\i18n\PhpMessageSource;
use yii2\extensions\filepond\Bootstrap;
use yii2\extensions\filepond\FilePond;
use yii2\extensions\filepond\tests\support\TestCase;

/**
 * Unit tests for {@see Bootstrap} message source registration.
 */
#[Group('bootstrap')]
final class BootstrapTest extends TestCase
{
    public function testKeepsApplicationMessageSource(): void
    {
        $this->mockWebApplication(
            [
                'bootstrap' => new ReplaceArrayValue([]),
                'components' => [
                    'i18n' => [
                        'translations' => [
                            FilePond::TRANSLATION_CATEGORY => ['class' => PhpMessageSource::class, 'basePath' => '@app'],
                        ],
                    ],
                ],
            ],
        );

        (new Bootstrap())->bootstrap(Yii::$app);

        $translations = Yii::$app->getI18n()->translations[FilePond::TRANSLATION_CATEGORY] ?? null;

        self::assertIsArray(
            $translations,
            'Configured source must not be replaced.',
        );
        self::assertSame(
            '@app',
            $translations['basePath'] ?? null,
            'Application configuration must win.',
        );
    }

    public function testRegistersMessageSourceWhenMissing(): void
    {
        $this->mockWebApplication(['bootstrap' => new ReplaceArrayValue([]), 'language' => 'de']);

        self::assertArrayNotHasKey(
            FilePond::TRANSLATION_CATEGORY,
            Yii::$app->getI18n()->translations,
            'Source must be absent before bootstrap.',
        );

        (new Bootstrap())->bootstrap(Yii::$app);

        self::assertSame(
            'Datei ist zu groß',
            Yii::t(FilePond::TRANSLATION_CATEGORY, 'File is too large'),
            'Messages must resolve from the package translations.',
        );
    }
}
