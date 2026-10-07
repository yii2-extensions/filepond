<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\asset;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

use function array_map;

/**
 * Unit tests for the {@see Plugin} enumeration metadata.
 */
#[Group('asset')]
final class PluginTest extends TestCase
{
    public function testAssetClassesDeliverTheirPlugin(): void
    {
        foreach (Plugin::cases() as $plugin) {
            $class = $plugin->assetClass();

            self::assertTrue(
                is_subclass_of($class, AbstractPluginAsset::class),
                "{$class} must be a plugin bundle.",
            );
            self::assertSame(
                $plugin,
                (new $class())->plugin(),
                "{$class} must deliver {$plugin->name}.",
            );
        }
    }

    public function testOptionsMatchFilePondPluginSwitches(): void
    {
        self::assertSame(
            [
                'allowFileEncode',
                'allowFilePoster',
                'allowFileRename',
                'allowFileSizeValidation',
                'allowFileTypeValidation',
                'allowImageCrop',
                'allowImageEdit',
                'allowImageExifOrientation',
                'allowImagePreview',
                'allowImageTransform',
                'allowPdfPreview',
            ],
            array_map(static fn(Plugin $plugin): string => $plugin->option(), Plugin::cases()),
            'Options must match the plugin switches.',
        );
    }

    public function testPackagesMatchNpmNames(): void
    {
        self::assertSame(
            [
                'filepond-plugin-file-encode',
                'filepond-plugin-file-poster',
                'filepond-plugin-file-rename',
                'filepond-plugin-file-validate-size',
                'filepond-plugin-file-validate-type',
                'filepond-plugin-image-crop',
                'filepond-plugin-image-edit',
                'filepond-plugin-image-exif-orientation',
                'filepond-plugin-image-preview',
                'filepond-plugin-image-transform',
                'filepond-plugin-pdf-preview',
            ],
            array_map(static fn(Plugin $plugin): string => $plugin->package(), Plugin::cases()),
            'Packages must match the npm names.',
        );
    }

    public function testStylesOnlyForPluginsShippingCss(): void
    {
        self::assertSame(
            [Plugin::FILE_POSTER, Plugin::IMAGE_EDIT, Plugin::IMAGE_PREVIEW, Plugin::PDF_PREVIEW],
            array_values(array_filter(Plugin::cases(), static fn(Plugin $plugin): bool => $plugin->hasStyles())),
            'Only plugins with a stylesheet must report styles.',
        );
    }

}
