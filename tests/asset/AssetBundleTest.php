<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\asset;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\InvalidConfigException;
use yii2\extensions\filepond\asset\{
    AbstractLocalAsset,
    AbstractNpmAsset,
    CropperAsset,
    FilePondAsset,
    FilePondCropperAsset,
    FilePondWidgetAsset,
    Plugin,
};
use yii2\extensions\filepond\asset\plugin\{FileEncodeAsset, ImageEditAsset, ImagePreviewAsset};
use yii2\extensions\filepond\exception\Message;
use yii2\extensions\filepond\tests\support\stub\DottedLocalAsset;
use yii2\extensions\filepond\tests\support\TestCase;

use function array_keys;
use function dirname;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function str_replace;

/**
 * Unit tests for {@see AbstractNpmAsset} and {@see AbstractLocalAsset} delivery modes and the concrete bundles.
 */
#[Group('asset')]
final class AssetBundleTest extends TestCase
{
    public function testApplicationConfigurationWinsOverRegisterWith(): void
    {
        $this->mockWebApplication(
            ['components' => ['assetManager' => ['bundles' => [FilePondAsset::class => ['cdn' => true]]]]],
        );

        $bundle = FilePondAsset::registerWith($this->view(), false);

        self::assertTrue(
            $bundle->cdn,
            'Application bundle configuration must take precedence.',
        );
    }

    public function testCdnBundleLinksInstalledVersion(): void
    {
        $this->mockWebApplication();

        $bundle = new ImagePreviewAsset(['cdn' => true, 'minified' => true]);

        self::assertNull(
            $bundle->sourcePath,
            'CDN mode must not publish local files.',
        );
        self::assertMatchesRegularExpression(
            '#^https://unpkg\.com/filepond-plugin-image-preview@\d+\.\d+\.\d+/dist/filepond-plugin-image-preview\.min\.js$#',
            self::url($bundle->js),
            'Script URL must pin the installed version.',
        );
        self::assertMatchesRegularExpression(
            '#/dist/filepond-plugin-image-preview\.min\.css$#',
            self::url($bundle->css),
            'Stylesheet URL must use the minified file.',
        );
    }

    public function testCdnUrlIsConfigurable(): void
    {
        $this->mockWebApplication();

        $bundle = new CropperAsset(['cdn' => true, 'cdnUrl' => 'https://cdn.jsdelivr.net/npm/']);

        self::assertStringStartsWith(
            'https://cdn.jsdelivr.net/npm/cropperjs@',
            self::url($bundle->js),
            'Custom CDN.',
        );
        self::assertSame(
            [],
            $bundle->css,
            'Cropper.js ships no stylesheet.',
        );
    }

    public function testLocalBundleMinifiedInsertsInfixBeforeLastExtension(): void
    {
        $this->mockWebApplication();

        $bundle = new DottedLocalAsset(['minified' => true]);

        self::assertSame(
            ['filepond.cropper.min.js', 'worker'],
            $bundle->js,
            'Infix must precede the last extension only; extensionless names stay unchanged.',
        );
        self::assertSame(
            ['filepond.cropper.theme.min.css'],
            $bundle->css,
            'Dots in the base name must be preserved.',
        );
    }

    public function testLocalBundleMinifiedSelectsMinFiles(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        $view->getAssetManager()->bundles = [
            FilePondCropperAsset::class => ['minified' => true],
            FilePondWidgetAsset::class => ['minified' => true],
        ];

        FilePondCropperAsset::register($view);

        $widget = $view->assetBundles[FilePondWidgetAsset::class] ?? null;
        $cropper = $view->assetBundles[FilePondCropperAsset::class] ?? null;

        self::assertInstanceOf(
            FilePondWidgetAsset::class,
            $widget,
            'Runtime bundle must be registered.',
        );
        self::assertInstanceOf(
            FilePondCropperAsset::class,
            $cropper,
            'Adapter bundle must be registered.',
        );
        self::assertSame(
            ['filepond-widget.min.css'],
            $widget->css,
            'Minified runtime stylesheet expected.',
        );
        self::assertSame(
            ['filepond-widget.min.js'],
            $widget->js,
            'Minified runtime script expected.',
        );
        self::assertSame(
            ['filepond-cropper.min.css'],
            $cropper->css,
            'Minified adapter stylesheet expected.',
        );
        self::assertSame(
            ['filepond-cropper.min.js'],
            $cropper->js,
            'Minified adapter script expected.',
        );
        self::assertSame(
            ['filepond-cropper.min.css', 'filepond-cropper.min.js'],
            $cropper->publishOptions['only'] ?? null,
            'Only the minified files.',
        );
        self::assertFileExists(
            "{$widget->basePath}/filepond-widget.min.js",
            'Minified runtime script must be published.',
        );
        self::assertFileExists(
            "{$cropper->basePath}/filepond-cropper.min.css",
            'Minified adapter stylesheet must be published.',
        );
    }

    public function testLocalBundlePublishesDistFiles(): void
    {
        $this->mockWebApplication();

        $bundle = FilePondAsset::registerWith($this->view());

        self::assertFalse(
            $bundle->cdn,
            'Local delivery is the default.',
        );
        self::assertSame(
            ['dist/filepond.css'],
            $bundle->css,
            'Debug mode must serve readable stylesheets.',
        );
        self::assertSame(
            ['dist/filepond.js'],
            $bundle->js,
            'Debug mode must serve readable scripts.',
        );
        self::assertSame(
            ['dist/filepond.css', 'dist/filepond.js'],
            $bundle->publishOptions['only'] ?? null,
            'Only dist.',
        );
        self::assertStringEndsWith(
            '/node_modules/filepond',
            str_replace('\\', '/', (string) $bundle->sourcePath),
            'Source must be `@npm`.',
        );
        self::assertStringEndsWith(
            '/assets/filepond',
            str_replace('\\', '/', (string) $bundle->basePath),
            'Bundle must be published.',
        );
    }

    public function testLocalBundlePublishesReadableFilesInDebugMode(): void
    {
        $this->mockWebApplication();

        $bundle = new FilePondWidgetAsset();

        self::assertFalse(
            $bundle->minified,
            'Debug mode must disable minification.',
        );
        self::assertSame(
            ['filepond-widget.css'],
            $bundle->css,
            'Readable stylesheet expected.',
        );
        self::assertSame(
            ['filepond-widget.css', 'filepond-widget.js'],
            $bundle->publishOptions['only'] ?? null,
            'Only the readable files.',
        );
    }

    public function testLocalBundlePublishOptionsOnlyIsNotOverridden(): void
    {
        $this->mockWebApplication();

        $bundle = new FilePondWidgetAsset(['publishOptions' => ['only' => ['*.js']]]);

        self::assertSame(
            ['*.js'],
            $bundle->publishOptions['only'] ?? null,
            'Configured patterns must be kept.',
        );
    }

    public function testLocalBundleResolvesSourcePathAlias(): void
    {
        $this->mockWebApplication();

        Yii::setAlias('@filepond-widget', dirname(__DIR__, 2) . '/src/asset/widget');

        $bundle = new FilePondWidgetAsset(['sourcePath' => '@filepond-widget/']);

        self::assertSame(
            dirname(__DIR__, 2) . '/src/asset/widget',
            $bundle->sourcePath,
            'Alias must be resolved without a trailing slash.',
        );
    }

    public function testMinifiedDefaultsToDebugMode(): void
    {
        $this->mockWebApplication();

        $bundle = new FilePondAsset();

        self::assertFalse(
            $bundle->minified,
            'Debug mode must disable minification.',
        );
    }

    public function testMinifiedSelectsMinFiles(): void
    {
        $this->mockWebApplication();

        $bundle = new FileEncodeAsset(['minified' => true]);

        self::assertSame(
            ['dist/filepond-plugin-file-encode.min.js'],
            $bundle->js,
            'Minified script expected.',
        );
        self::assertSame(
            [],
            $bundle->css,
            'File Encode ships no stylesheet.',
        );
    }

    public function testPluginBundleDependsOnCore(): void
    {
        $this->mockWebApplication();

        $bundle = new ImageEditAsset();

        self::assertSame(
            Plugin::IMAGE_EDIT,
            $bundle->plugin(),
            'Bundle must expose its plugin.',
        );
        self::assertSame(
            [FilePondAsset::class],
            $bundle->depends,
            'Plugins must depend on the core bundle.',
        );
        self::assertSame(
            [FilePondAsset::class],
            ImageEditAsset::dependencies(),
            'Dependencies must be exposed.',
        );
        self::assertSame(
            [],
            FilePondAsset::dependencies(),
            'Core bundle must not depend on other bundles.',
        );
    }

    public function testPublishOptionsOnlyIsNotOverridden(): void
    {
        $this->mockWebApplication();

        $bundle = new FilePondAsset(['publishOptions' => ['only' => ['dist/*']]]);

        self::assertSame(
            ['dist/*'],
            $bundle->publishOptions['only'] ?? null,
            'Configured patterns must be kept.',
        );
    }

    public function testRegisterWithSeedsDependenciesAndKeepsFirstMode(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        $plugin = ImageEditAsset::registerWith($view, true);

        $core = $view->assetBundles[FilePondAsset::class] ?? null;

        $again = ImageEditAsset::registerWith($view, false);

        self::assertInstanceOf(
            FilePondAsset::class,
            $core,
            'Dependency must be registered.',
        );
        self::assertTrue(
            $plugin->cdn,
            'Requested mode must apply to the bundle.',
        );
        self::assertTrue(
            $core->cdn,
            'Requested mode must propagate to npm dependencies.',
        );
        self::assertSame(
            $plugin,
            $again,
            'A registered bundle must keep its mode.',
        );
    }

    public function testThrowInvalidConfigExceptionWhenPackageManifestIsMissing(): void
    {
        $this->mockWebApplication();

        Yii::setAlias('@npm', self::RUNTIME_PATH . '/missing');

        try {
            $this->expectException(InvalidConfigException::class);
            $this->expectExceptionMessage(
                Message::PACKAGE_VERSION_UNAVAILABLE->getMessage(
                    'cropperjs',
                    self::RUNTIME_PATH . '/missing/cropperjs/package.json',
                ),
            );

            new CropperAsset(['cdn' => true]);
        } finally {
            Yii::setAlias('@npm', dirname(__DIR__, 2) . '/node_modules');
        }
    }

    public function testThrowInvalidConfigExceptionWhenPackageVersionIsMissing(): void
    {
        $this->mockWebApplication();

        $path = self::RUNTIME_PATH . '/npm/filepond';

        if (!is_dir($path)) {
            mkdir($path, 0o777, true);
        }

        file_put_contents("{$path}/package.json", '{"name":"filepond"}');

        Yii::setAlias('@npm', self::RUNTIME_PATH . '/npm');

        try {
            $this->expectException(InvalidConfigException::class);
            $this->expectExceptionMessage(
                Message::PACKAGE_VERSION_UNAVAILABLE->getMessage('filepond', "{$path}/package.json"),
            );

            new FilePondAsset(['cdn' => true]);
        } finally {
            Yii::setAlias('@npm', dirname(__DIR__, 2) . '/node_modules');
        }
    }

    public function testWidgetAndCropperBundlesPublishPackageFiles(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePondCropperAsset::register($view);

        $bundles = array_keys($view->assetBundles);
        $widget = $view->assetBundles[FilePondWidgetAsset::class] ?? null;
        $cropper = $view->assetBundles[FilePondCropperAsset::class] ?? null;

        self::assertEqualsCanonicalizing(
            [
                CropperAsset::class,
                FilePondAsset::class,
                FilePondCropperAsset::class,
                FilePondWidgetAsset::class,
                ImageEditAsset::class,
            ],
            $bundles,
            'Adapter dependencies must be registered.',
        );
        self::assertInstanceOf(
            FilePondWidgetAsset::class,
            $widget,
            'Runtime bundle must be registered.',
        );
        self::assertInstanceOf(
            FilePondCropperAsset::class,
            $cropper,
            'Adapter bundle must be registered.',
        );
        self::assertSame(
            ['filepond-widget.js'],
            $widget->js,
            'Runtime script expected.',
        );
        self::assertSame(
            ['filepond-cropper.css'],
            $cropper->css,
            'Adapter stylesheet expected.',
        );
        self::assertFileExists(
            "{$widget->basePath}/filepond-widget.js",
            'Runtime script must be published.',
        );
        self::assertFileExists(
            "{$cropper->basePath}/filepond-cropper.js",
            'Adapter script must be published.',
        );
    }

    /**
     * @param array<array-key, mixed> $files
     */
    private static function url(array $files): string
    {
        $url = $files[0] ?? null;

        self::assertIsString($url, 'First asset file must be a string.');

        return $url;
    }
}
