<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests;

use Yii2\Extensions\FilePond\Asset;
use Yii;
use yii\web\AssetBundle;
use yii\web\View;

final class AssetTest extends TestCase
{
    public function testFilePondAssetSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondAsset::register($view);

        $this->assertCount(6, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $view->assetBundles);
    }

    public function testFilePondAssetRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondAsset::register($view);

        $this->assertCount(6, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondAsset::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString('filepond.css', $result);
        $this->assertStringContainsString('filepond.js', $result);
        $this->assertStringContainsString('filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('filepond-plugin-file-validate-type.js', $result);
    }

    public function testFilePondCdnAssetSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondCdnAsset::register($view);

        $this->assertCount(6, $view->assetBundles);

        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $view->assetBundles);
    }

    public function testFilePondCdnAssetRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondCdnAsset::register($view);

        $this->assertCount(6, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondCdnAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\Cdn\FilePondEncodePlugin::class]);
        $this->assertInstanceOf(
            AssetBundle::class,
            $view->assetBundles[Asset\Cdn\FilePondImageExifOrientationPlugin::class],
        );
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\Cdn\FilePondImagePreviewPlugin::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\Cdn\FilePondValidateSizePlugin::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\Cdn\FilePondValidateTypePlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondImageCropPluginSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondImageCropPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageCropPlugin::class, $view->assetBundles);
    }

    public function testFilePondImageCropPluginRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondImageCropPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondImageCropPlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString('filepond.css', $result);
        $this->assertStringContainsString('filepond.js', $result);
        $this->assertStringContainsString('filepond-plugin-image-crop.js', $result);
    }

    public function testFilePondImageCropPluginCdnSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondImageCropPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageCropPlugin::class, $view->assetBundles);
    }

    public function testFilePondImageCropPluginCdnRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondImageCropPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondCdnAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\Cdn\FilePondImageCropPlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondImageTransformPluginSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondImageTransformPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageTransformPlugin::class, $view->assetBundles);
    }

    public function testFilePondImageTransformPluginRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondImageTransformPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondImageTransformPlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString('filepond.css', $result);
        $this->assertStringContainsString('filepond.js', $result);
        $this->assertStringContainsString('filepond-plugin-image-transform.js', $result);
    }

    public function testFilePondImageTransformPluginCdnSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondImageTransformPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageTransformPlugin::class, $view->assetBundles);
    }

    public function testFilePondImageTransformPluginCdnRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondImageTransformPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondCdnAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\Cdn\FilePondImageTransformPlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondPdfPreviewPluginSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondPdfPreviewPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondPdfPreviewPlugin::class, $view->assetBundles);
    }

    public function testFilePondPdfPreviewPluginRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondPdfPreviewPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondPdfPreviewPlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString('filepond.css', $result);
        $this->assertStringContainsString('filepond.js', $result);
        $this->assertStringContainsString('filepond-plugin-pdf-preview.js', $result);
    }

    public function testFilePondPdfPreviewPluginCdnSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondPdfPreviewPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondPdfPreviewPlugin::class, $view->assetBundles);
    }

    public function testFilePondPdfPreviewPluginCdnRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondPdfPreviewPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondCdnAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\Cdn\FilePondPdfPreviewPlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
    }

    public function testFilePondRenamePluginSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondRenamePlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondRenamePlugin::class, $view->assetBundles);
    }

    public function testFilePondRenamePluginRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\FilePondRenamePlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondRenamePlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString('filepond.css', $result);
        $this->assertStringContainsString('filepond.js', $result);
        $this->assertStringContainsString('filepond-plugin-file-rename.js', $result);
    }

    public function testFilePondRenamePluginCdnSimpleDependency(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondRenamePlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondRenamePlugin::class, $view->assetBundles);
    }

    public function testFilePondRenamePluginCdnRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondRenamePlugin::register($view);

        $this->assertCount(7, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\FilePondCdnAsset::class]);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[Asset\Cdn\FilePondRenamePlugin::class]);

        $result = $view->renderFile(__DIR__ . '/Support/main.php');

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-rename/dist/filepond-plugin-file-rename.js"></script>
            HTML,
            $result,
        );
    }
}
